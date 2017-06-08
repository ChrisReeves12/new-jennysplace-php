<?php
/**
* The DataFixController class definition.
*
* This console controller handles updating of database records, primarily from cron jobs
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Console\Controller;

use Doctrine\ORM\EntityManager;
use Library\Controller\JPController;
use Library\Model\Category\Category;
use Library\Model\Product\Product;
use Library\Model\Product\Sku;
use Library\Model\Product\Status;
use Library\Model\Relationship\ProductCategory;
use Library\Service\DB\EntityManagerSingleton;
use MongoDB\Client as MongoDBClient;

/**
 * Class DataFixController
 * @package Console\Controller
 */
class DataFixController extends JPController
{
    /**
     * Updates the database schema from the domain model
     */
    public function updatedatabaseschemaAction()
    {
        $db_service = $this->getServiceLocator()->get('data');
        $db_service->updateDatabase();
        echo "Database updated\n";
        exit(0);
    }

    /**
     * Updates stock statuses of products by observing their skus and status overrides
     */
    public function updateproductstatusAction()
    {
        try
        {
            $em = EntityManagerSingleton::getInstance();

            $in_stock = $em->getReference('Library\Model\Product\Status', 1);
            $out_of_stock = $em->getReference('Library\Model\Product\Status', 2);
            $disabled = $em->getReference('Library\Model\Product\Status', 3);
            $preorder = $em->getReference('Library\Model\Product\Status', 5);
            $count = 0;

            echo "--- Updating product statuses ---\n";

            echo "--- Loading up all the products ---\n";
            $products = $em->getRepository('Library\Model\Product\Product')->findAll();

            /** @var Product $product */
            foreach ($products as $product)
            {
                echo "-- Evaluating product {$product->getId()}\n";

                if ($product->getStatusOverride() instanceof Status)
                {
                    $product->setStatus($product->getStatusOverride());
                } else
                {
                    // If this has a default sku set, use the status override or quantity to get status
                    if (count($product->getSkus()) == 1 && ($product->getDefaultSku() instanceof Sku))
                    {
                        // If there is no status override, use quantity to determine status
                        if ($product->getDefaultSku()->getQuantity() <= 0)
                        {
                            $product->getDefaultSku()->setQuantity(0);
                            $product->getDefaultSku()->setStatus($out_of_stock);
                            $product->setStatus($out_of_stock);
                        } else
                        {
                            $product->setStatus($in_stock);
                            $product->getDefaultSku()->setStatus($in_stock);
                        }
                    } else
                    {
                        // Get status from skus
                        $all_out_of_stock = true;
                        $all_disabled = true;
                        $all_preorder = true;

                        $product_skus = $product->getSkus();

                        foreach ($product_skus as $product_sku)
                        {
                            // This should never actually run because we're not suppose to have default skus here
                            if ($product_sku->getIsDefault()) continue;

                            if ($product_sku->getStatus() != $out_of_stock) $all_out_of_stock = false;

                            if ($product_sku->getStatus() != $disabled) $all_disabled = false;

                            if ($product_sku->getStatus() != $preorder) $all_preorder = false;
                        }

                        if ($all_out_of_stock == true) $product->setStatus($out_of_stock); elseif ($all_disabled == true) $product->setStatus($disabled);
                        elseif ($all_preorder == true) $product->setStatus($preorder);
                        else
                            $product->setStatus($in_stock);
                    }
                }

                $count++;
            }

            echo "--- Flushing changes ---\n";
            $em->flush();
            $em->clear();

            echo "--- Done update products! ---\n";
            echo "--- Updated {$count} products ---\n";
        }
        catch (\Exception $ex)
        {
            echo "Error: " . $ex->getMessage() . "\n";
            exit(1);
        }
        exit(0);
    }

    /**
     * Update the product option cache in Mongo DB
     */
    public function updateProductOptionCacheAction()
    {
        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity_manager');

        /** @var MongoDBClient $mongoClient */
        $mongoClient = $this->getServiceLocator()->get('mongo_db');
        $collection = $mongoClient->newjennysplace->product_options_cache;

        $connection = $em->getConnection();
        $products = $connection->fetchAll("SELECT DISTINCT product_id FROM skus WHERE is_default = 0");
        foreach($products as $product)
        {
            $id = intval($product['product_id']);
            $product_obj = $em->getReference('Library\Model\Product\Product', $id);
            $product_options_document = new \StdClass();
            $options = $em->getRepository('Library\Model\Product\Option')->findByProduct($id);
            $option_value_map = [];

            if (count($options) > 0)
            {
                $skus = $product_obj->getSkus();

                foreach ($options as $option)
                {
                    if (!isset($option_value_map[$option->getId()]))
                    {
                        $option_value_map[$option->getId()] = [];
                    }

                    // Go through each sku and get the available values
                    foreach ($skus as $sku)
                    {
                        $sku_option_option_values = $sku->getSkuOptionOptionValues()->toArray();
                        if (count($sku_option_option_values) > 0)
                        {
                            foreach ($sku_option_option_values as $sku_option_option_value)
                            {
                                $sku_option = $sku_option_option_value->getOptionOptionValue()->getOption();
                                $sku_option_value = $sku_option_option_value->getOptionOptionValue()->getOptionValue();

                                // If there is a match, add this to the table
                                if ($option->getId() == $sku_option->getId())
                                {
                                    $option_value_map[$option->getId()][$option->getId()] = $option;
                                    $option_value_map[$option->getId()]['values'][$sku_option_value->getId()] = $sku_option_value;
                                }
                            }
                        }
                    }
                }

                $product_options_document->product_id = $id;
                $product_options_document->name = $product_obj->getName();
                $product_options_document->product_code = $product_obj->getProductCode();
                $product_options_document->options = [];

                foreach($option_value_map as $key => $value)
                {
                    $option = $value[$key];
                    $option_values = $value['values'];
                    $product_options_document->options[$option->getId()] = [];
                    foreach($option_values as $option_value)
                    {
                        $product_options_document->options[$option->getId()][] = ['id' => $option_value->getId(), 'option_name' => $option->getName(), 'name' => $option_value->getName()];
                    }

                    $result = $collection->findOne(['product_id' => $id]);
                    if (empty($result))
                    {
                        // Create a new document
                        $collection->insertOne($product_options_document);
                        echo "Adding product " . $id . " option cache\n";
                    }
                    else
                    {
                        // Update document
                        $collection->replaceOne(['product_id' => $id], $product_options_document);
                        echo "Updating product " . $id . " option cache\n";
                    }
                }
            }
        }

        echo "Complete!\n";
        exit(0);
    }

    /**
     * Fixes sku statuses to match products
     */
    public function fixskustatusAction()
    {
        echo "--Updating statuses of skus that are missing statuses--\n";
        $em = $this->getServiceLocator()->get('entity_manager');
        $results = $em->getRepository('Library\Model\Product\Sku')->findBy(['status' => null]);
        if (count($results) > 0)
        {
            foreach ($results as $result)
            {
                $product = $result->getProduct();
                $result->setStatus($product->getStatus());
            }

            $em->flush();
        }
        echo "--Finished updating statuses--\n";
    }

    /**
     * Moves products from one category to the other
     * @throws \Doctrine\DBAL\DBALException
     */
    public function categoryproductmoveAction()
    {
        $source_cat = $this->getRequest()->getParam('source_cat');
        $dest_cat = $this->getRequest()->getParam('dest_cat');

        // Load all the product ids that are in the source category
        $em = EntityManagerSingleton::getInstance();
        $connection = $em->getConnection();
        $connection->executeQuery("UPDATE assoc_products_categories SET category_id = :dest_cat WHERE category_id = :source_cat",
            ['source_cat' => $source_cat, 'dest_cat' => $dest_cat]);
    }

    /**
     * Moves orphaned products to uncategorized category
     */
    public function moveorphanedproductsAction()
    {
        $em = EntityManagerSingleton::getInstance();
        $connection = $em->getConnection();
        $results = $connection->fetchAll('select id from products where id not in (select distinct(product_id) from assoc_products_categories)');

        // Load products and put them in the uncategorized category
        /** @var Category $uncategorized */
        $uncategorized = $em->getReference('Library\Model\Category\Category', 9999);
        $count = 0;

        echo "--- Moving orphaned products ---\n";
        foreach ($results as $result)
        {
            /** @var Product $product */
            $product = $em->getRepository('Library\Model\Product\Product')->find($result['id']);
            $product_category = new ProductCategory();
            $product_category->setCategory($uncategorized);
            $product_category->setProduct($product);
            $product_category->setSortOrder(0);
            $em->persist($product_category);
            $count++;
        }

        $em->flush();
        $em->clear();
        echo "--- Moved ${count} orphaned products ---\n";
        exit(0);
    }

    /**
     * Update settings json file to xml
     */
    public function convertsettingsfileAction()
    {
        $writer = new \Zend\Config\Writer\Xml();

        $settings = json_decode(file_get_contents(getcwd() . '/config/settings.json'), true);
        $xml_content = $writer->processConfig($settings);
        file_put_contents(getcwd() . '/config/settings.xml', $xml_content);
        echo "File written\n";
    }

    /**
     * Deletes duplicate product associations from the database
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteduplicatecategoryassociationsAction()
    {
        echo "--- Deleting Duplicate Product-Category Associations ---\n";

        // Go through each product category and check for duplicates
        $em = EntityManagerSingleton::getInstance();
        $connection = $em->getConnection();
        $duplicates_count = 0;

        echo "--- Getting associations to process ---\n";
        $product_category_rows = $connection->fetchAll("SELECT id FROM assoc_products_categories");

        echo "--- Processing... ---\n";
        foreach($product_category_rows as $product_category_row)
        {
            $assoc_id = $product_category_row['id'];

            // Load this association
            $product_category = $em->getRepository('Library\Model\Relationship\ProductCategory')->find($assoc_id);
            if ($product_category instanceof ProductCategory)
            {
                // Check that there is only of these
                $count = $connection->fetchAssoc("SELECT COUNT(id) as count FROM assoc_products_categories WHERE product_id = :product_id AND category_id = :category_id",
                    ['product_id' => $product_category->getProduct()->getId(), 'category_id' => $product_category->getCategory()->getId()])['count'];

                // If there is a duplicate, the number will be more than 1
                if ($count > 1)
                {
                    $duplicates_count++;
                    echo "Duplicate Found in Association ID: {$product_category->getId()}\n";

                    // Delete all of the occurences of this duplicate excluding the first one found
                    $connection->executeQuery("DELETE FROM assoc_products_categories WHERE category_id = :category_id AND product_id = :product_id AND id != :assoc_id",
                        ['category_id' => $product_category->getCategory()->getId(), 'product_id' => $product_category->getProduct()->getId(), 'assoc_id' => $product_category->getId()]);

                }
            }
        }

        // Close out
        echo "Job Complete! \n";
        echo "Delete ${duplicates_count} duplicates.\n";
        exit(0);
    }

    /**
     * Update query lists in the database
     */
    public function updatequerylistsAction()
    {
        $db_service = $this->getServiceLocator()->get('data');
        $db_service->buildQueryList('Top Sellers');
        echo "Top Sellers Updated\n";

        $db_service->buildQueryList('New Products');
        echo "New Products Updated\n";

        $db_service->buildQueryList('Sale List');
        echo "Sale List Updated\n";

        exit(0);
    }
}