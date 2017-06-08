<?php
/**
* The ProductService class definition.
*
* This class administers and works with Product entities.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Category\Category;
use Library\Model\Media\Video;
use Library\Model\Product\Status;
use Library\Model\Relationship\ProductCategory;
use Library\Model\Relationship\ProductVideo;
use Library\Service\DB\EntityManagerSingleton;
use Library\Model\Product\Product;
use Library\Model\Media\Image;
use Library\Model\Page\Page;
use Library\Model\Product\Sku;

/**
 * Class ProductService
 * @package Library\Service
 */
class ProductService extends AbstractService
{
    /**
     * Save or update a product.
     * @param array $data
     * @param int $product_id
     *
     * @return Product $product
     * @throws \Exception
     */
    public function save($data, $product_id = null)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $page_service = $this->getServiceManager()->get('page');

        // If there is a product id, the product should be saved, not created.
        if (!empty($product_id))
        {
            $product = $em->getRepository('Library\Model\Product\Product')->findOneById($product_id);
            if (false === ($product instanceof Product))
            {
                throw new \Exception("The product being updated does not exist.");
            }
        }
        else
        {
            $product = null;
        }

        // Default image object
        if (!empty($data['default_image_id']))
        {
            $image = $em->getRepository('Library\Model\Media\Image')->findOneById($data['default_image_id']);

            if (!($image instanceof Image))
            {
                throw new \Exception("The main image for this product was not uploaded correctly. Please try again.");
            }

            $image->setAlt($data['name']);
            $image->setTitle($data['name']);
        }
        else
        {
            $image = null;
        }

        // Get the page object
        $page = is_null($product) ? new Page() : $product->getPage();
        $page->setTitle($data['name']);
        $page->setPageType('product');
        $page->setDescription($data['meta_description']);
        $page->setKeywords($data['keywords']);

        // Create a handle if one has not been given
        $page_handle = $page->getUrlHandle();
        if (empty($page_handle))
        {
            $page->setUrlHandle($page_service->create_handle($data['name']));
        }

        $page->setAccess(1);
        $page->setInactive(false);
        $em->persist($page);

        // Save the product
        $product = is_null($product) ? new Product() : $product;
        $skus = $product->getSkus();
        $product->setName($data['name']);
        $product->setBasePrice($data['base_price']);
        $product->setDescription($data['description']);
        $product->setKeywords($data['keywords']);
        $product->setBaseWeight($data['base_weight']);
        $product->setProductCode($data['product_code']);
        $product->setSortOrder($data['sort_order']);
        $product->setShowMoreCaption($data['show_more_caption']);
        $product->setTax($data['tax']);
        $product->setDefaultImage($image);
        $product->setDiscountPrice($data['discount_price']);
        $product->setPage($page);

        // Save date added if provided
        if (!empty($data['date_added']))
        {
            $date = new \DateTime();
            $date = $date->createFromFormat('m/d/Y', $data['date_added']);
            $product->setDateCreated($date);
        }
        else
        {
            // Use current date and time if none provided
            $product->setDateCreated(new \DateTime());
        }

        $status_override = $em->getRepository('Library\Model\Product\Status')->findOneById($data['status_override']);
        $product->setStatusOverride($status_override);

        // Add a default sku to new product
        if (is_null($product_id))
        {
            $sku = new Sku();
            $sku->setStatus($status_override);
            $sku->setQuantity($data['quantity']);
            $sku->setProduct($product);
            $sku->setIsDefault(true);
            $em->persist($sku);
        }
        else
        {
            // Skus are only updated in this routine when the product only has one sku, multiple skus are updated in the save_options routine
            if (count($skus) == 1)
            {
                $sku = $skus[0];
                $sku->setStatus($status_override);
                $sku->setQuantity($data['quantity']);
                $sku->setProduct($product);
                $sku->setNumber($product->getProductCode());
                $sku->setIsDefault(true);
            }
            elseif (count($skus) < 1)
            {
                // We should never get here because the product being edited has no sku. Create a sku for the product.
                $sku = new Sku();
                $sku->setStatus($status_override);
                $sku->setQuantity($data['quantity']);
                $sku->setProduct($product);
                $sku->setIsDefault(true);
                $em->persist($sku);
            }
        }

        // Clear out existing category relationships if any
        $change_data = [];

        $old_category_relationships = $product->getProductCategories();
        if (count($old_category_relationships) > 0)
        {
            foreach ($old_category_relationships as $category_relationship)
            {
                $change_data[$category_relationship->getCategory()->getId()] = [];
                $change_data[$category_relationship->getCategory()->getId()] = $category_relationship->getSortOrder() ?? 0;
                $em->remove($category_relationship);
            }
        }

        // Load up categories to add products to them
        $category_ids = explode(',', $data['category_list']);
        $categories = $em->getRepository('Library\Model\Category\Category')->findBy(['id' => $category_ids]);

        if (!empty($categories))
        {
            /** @var Category $category */
            foreach ($categories as $category)
            {
                // If the user added a theme category, skip it because those should be added with the check box list for themes
                if ($category->getId() == Category::THEME_CATEGORY_ID || (!is_null($category->getParentCategory()) && $category->getParentCategory()->getId() == Category::THEME_CATEGORY_ID))
                    continue;

                // If the product used this category, use the old sort order
                if (array_key_exists($category->getId(), $change_data))
                    $sort_order = $change_data[$category->getId()];
                else
                    $sort_order = 0;

                $category->addProduct($product, $sort_order);
                $em->persist($category);
            }
        }
        else
        {
            throw new \Exception("All products must be associated with at least one category.");
        }

        // Load up themes to add to product
        $theme_ids = explode(',', $data['theme_list']);
        if (count($theme_ids) > 0)
        {
            $themes = $em->getRepository('Library\Model\Category\Category')->findBy(['id' => $theme_ids]);
        }

        if (!empty($themes))
        {
            /** @var Category $theme */
            foreach ($themes as $theme)
            {
                // Use the old sort order for themes as well
                if (array_key_exists($theme->getId(), $change_data))
                    $sort_order = $change_data[$theme->getId()];
                else
                    $sort_order = 0;

                $theme->addProduct($product, $sort_order);
                $em->persist($theme);
            }
        }

        $em->persist($product);

        return $product;
    }

    /**
     * Updates the 'important' statuses of the products by id
     *
     * @param int[] $ids_to_make_important
     * @param int[] $ids_to_make_normal
     */
    public function updateImportantByIds($ids_to_make_important = [], $ids_to_make_normal = [])
    {
        // Get products
        $ids = !empty($ids_to_make_important) ? array_merge($ids_to_make_important, $ids_to_make_normal) : $ids_to_make_normal;
        $em = EntityManagerSingleton::getInstance();
        $products = $em->getRepository('Library\Model\Product\Product')->findBy(['id' => $ids]);

        if (count($products) > 0)
        {
            /** @var Product $product */
            foreach ($products as $product)
            {
                if (in_array($product->getId(), $ids_to_make_important))
                    $product->setImportant(true);
                elseif (in_array($product->getId(), $ids_to_make_normal))
                    $product->setImportant(false);
            }
        }
    }

    /**
     * Adds additional videos to products
     *
     * @param $data
     * @param $product
     *
     * @return ProductVideo
     */
    public function addAdditionalVideo($data, $product)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $type = strtolower($data['type']);
        $url = $data['url'];

        $video = new Video();
        $video->setType($type);
        $video->setUrl($url);

        // Add new association
        $product_video = new ProductVideo();
        $product_video->setProduct($product);
        $product_video->setVideo($video);
        $em->persist($product_video);
        $em->persist($video);
        $em->persist($product);
;
        return $product_video;
    }

    /**
     * Updates the additional images on the product from a data array
     *
     * @param array $data
     * @param int $product_id
     *
     * @return \Library\Model\Product\Product $product
     * @throws \Exception
     */
    public function addAdditionalImages($data, $product_id)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Load the product
        $product = $em->getRepository('Library\Model\Product\Product')->findOneById($product_id);
        if (false === ($product instanceof Product))
        {
            throw new \Exception("The product id that was passed in does not represent a product in the database.");
        }

        if (!empty($data))
        {
            // Add each image
            foreach ($data['image'] as $image_item)
            {
                $image = new Image();
                $image_service = $this->getServiceManager()->get('image');
                $image->setUrl($image_service->getFileNameFromTempName($image_item['tmp_name']));
                $image->setAlt($product->getName());
                $image->setInactive(true);
                $image->setTitle($product->getName());
                $em->persist($image);

                if (isset($image_item['sort_order']))
                {
                    $sort_order = $image_item['sort_order'];
                }
                else
                {
                    $sort_order = null;
                }

                $product->addAdditionalImage($image, $sort_order);
            }
        }

        return $product;
    }

    /**
     * Updates statuses on products
     * @param int[] $ids
     * @param int $status
     *
     * @return Status
     */
    public function updateStatusesByIds($ids, $status)
    {
        // Load products
        $em = EntityManagerSingleton::getInstance();
        $products = $em->getRepository('Library\Model\Product\Product')->findBy(['id' => $ids]);
        $status_obj = ($status > 0) ? $em->getRepository('Library\Model\Product\Status')->findOneById($status) : null;

        /** @var Product $product */
        foreach ($products as $product)
        {
            $product->setStatusOverride($status_obj);
        }

        return $status_obj;
    }

    /**
     * Updates categories of product ids passed in
     * @param int[] $ids
     * @param int[] $category_ids
     * @param string $save_method
     *
     * @throws \Exception
     */
    public function updateCategoriesByIds($ids, $category_ids, $save_method)
    {
        $em = EntityManagerSingleton::getInstance();

        $products = $em->getRepository('Library\Model\Product\Product')->findBy(['id' => $ids]);
        $categories = $em->getRepository('Library\Model\Category\Category')->findBy(['id' => $category_ids]);

        // First get all of the categories each item belongs too
        $change_info = [];
        /** @var Product $product */
        foreach ($products as $product)
        {
            $change_info[$product->getId()] = [];
            $product_categories = $product->getProductCategories();

            /** @var ProductCategory $product_category */
            foreach ($product_categories as $product_category)
            {
                $change_info[$product->getId()][$product_category->getCategory()->getId()] = [];
                $change_info[$product->getId()][$product_category->getCategory()->getId()] = $product_category->getSortOrder();
            }
        }

        // Delete all product relationships out of the system for product if in change mode
        if ($save_method == "change")
        {
            $statement = $em->getConnection()->prepare("DELETE FROM assoc_products_categories WHERE product_id = :product_id");

            foreach ($ids as $product_id)
            {
                $statement->bindValue("product_id", $product_id);
                $statement->execute();
            }
        }

        // Insert new relationships into database
        /** @var Product $product */
        foreach ($products as $product)
        {
            /** @var Category $category */
            foreach ($categories as $category)
            {
                // If this item was in this category, use the old sort order
                if (array_key_exists($category->getId(), $change_info[$product->getId()]))
                {
                    // In add mode, duplicate product-category relationship will be skipped
                    if ($save_method == "add")
                        continue;
                    elseif($save_method == "change")
                    {
                        // In change mode, for relationships that existed before, use the sort order they used before
                        $product_category = new ProductCategory();
                        $product_category->setProduct($product);
                        $product_category->setCategory($category);
                        $product_category->setSortOrder($change_info[$product->getId()][$category->getId()]);
                        $em->persist($product_category);
                    }
                }
                else
                {
                    // This is a new category relationship
                    $product_category = new ProductCategory();
                    $product_category->setProduct($product);
                    $product_category->setCategory($category);
                    $product_category->setSortOrder(0);
                    $em->persist($product_category);
                }
            }
        }
    }

    /**
     * Updates statuses on products
     * @param int[] $ids
     * @param string $str_date
     *
     * @return Status
     */
    public function updateDateAddedByIds($ids, $str_date)
    {
        // Load products
        $em = EntityManagerSingleton::getInstance();
        $products = $em->getRepository('Library\Model\Product\Product')->findBy(['id' => $ids]);
        $date = new \DateTime();
        $date = $date->createFromFormat('m/d/Y', $str_date);

        /** @var Product $product */
        foreach ($products as $product)
        {
            $product->setDateCreated($date);
        }
    }
}