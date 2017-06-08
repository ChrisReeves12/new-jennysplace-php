<?php
/**
* The ProductController class definition.
*
* Controls all of the functions used by the product display page
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\Controller;

use Library\Controller\JPController;
use Library\Model\Page\Page;
use Library\Model\Product\Product;
use Library\Model\Product\Sku;
use Library\Model\Product\Status;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;
use Library\Model\Media\Image;
use Zend\View\Model\JsonModel;

/**
 * Class ProductController
 * @package Frontend\Controller
 */
class ProductController extends JPController
{
    /**
     * The main page of the product display page
     * @return array
     */
    public function indexAction()
    {
        // Return JSON for ajax requests
        $response = $this->_handle_ajax_requests();
        if (!empty($response))
        {
            return new JsonModel($response);
        }

        // Set intermediate layout
        $main_layout = $this->layout()->getChildrenByCaptureTo('main_layout')[0];
        $main_layout->setTemplate('frontend/layout/shopping_layout');

        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Load page and product
        $handle = $this->params()->fromRoute('handle');
        $page = $em->getRepository('Library\Model\Page\Page')->findOneBy(['url_handle' => $handle, 'inactive' => false]);
        $product = $sku_id = $options = $option_value_map = $extra_photos = $videos = $product_status = null;

        // Check if page is valid and set it
        if ($page instanceof Page)
        {
            /** @var Product $product */
            $product = $em->getRepository('Library\Model\Product\Product')->findOneByPage($page);

            // Add core javascript
            $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/frontend/product.js');

            $this->setPage($page);

            // Get status
            $status = $product->getStatus();
            if ($status instanceof Status)
            {
                $product_status = $status->getName();
            }
        }

        // Check for product validity and get skus
        if ($product instanceof Product)
        {
            $skus = $product->getSkus();
            if (count($skus) == 1)
            {
                $sku_id = $skus[0]->getId();
            }
            else
            {
                $option_value_map = [];

                // Check first if the option info has already been cached
                $mongoClient = $this->getServiceLocator()->get('mongo_db');
                $collection = $mongoClient->newjennysplace->product_options_cache;

                $product_option_document = $collection->findOne(['product_id' => $product->getId()]);
                if(!empty($product_option_document))
                {
                    // Get values from product option document
                    foreach($product_option_document->options as $option_id => $option_info)
                    {
                        $option_value_map[$option_id] = [$option_id => $em->getReference('Library\Model\Product\Option', $option_id), 'values' => []];
                        foreach($option_info as $option_value_info)
                        {
                            $option_value_map[$option_id]['values'][$option_value_info->id] = $em->getReference('Library\Model\Product\OptionValue', $option_value_info->id);
                        }
                    }
                }
                else
                {
                    // Get options from skus
                    $options = $em->getRepository('Library\Model\Product\Option')->findByProduct($product->getId());

                    // Get available values
                    if (count($options) > 0)
                    {
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
                    }
                }
            }

            // Get videos and photos for product
            $videos = $em->getRepository('Library\Model\Relationship\ProductVideo')->findBy(['product' => $product], ['sort_order' => 'DESC']);
            $extra_photos = $em->getRepository('Library\Model\Relationship\ProductImage')->findBy(['product' => $product], ['sort_order' => 'DESC']);
        }

        return ['product' => $product, 'product_status' => $product_status, 'option_value_map' => $option_value_map, 'extra_photos' => $extra_photos, 'sku_id' => $sku_id, 'videos' => $videos];
    }

    /**
     * Handle various ajax requests
     * @return array
     */
    private function _handle_ajax_requests()
    {
        $task = $_GET['task'] ?? null;

        if ($task == 'change_sku')
        {
            $data = $_GET;
            $options = $_GET['selected_info'];

            // Get product
            $em = EntityManagerSingleton::getInstance();
            $product = $em->getRepository('Library\Model\Product\Product')->findOneById($data['product_id']);

            // Find sku by options if options are there
            if (!empty($options))
            {
                // Get sku from options
                $sku = $em->getRepository('Library\Model\Product\Sku')->findOneByOptions($product, $options);

            }
            else
            {
                // Get default sku if product has no options
                $sku = $product->getSkus()[0];
            }

            // Return information to jquery regarding sku
            if (($sku instanceof Sku) && !$sku->getIsDefault())
            {
                $image_path = Settings::get('image_path');

                // Get correct image
                if ($sku->getImage() instanceof Image)
                {
                    $image = $image_path . '/product_images/' . $sku->getImage()->getUrl();
                }
                elseif ($sku->getProduct()->getDefaultImage() instanceof Image)
                {
                    $image = $image_path . '/product_images/' . $sku->getProduct()->getDefaultImage()->getUrl();
                }
                else
                {
                    $image = $image_path . '/layout_images/no_photo.jpg';
                }

                return [
                    'error' => false,
                    'sku_info' => [
                        'id' => $sku->getId(),
                        'number' => $sku->getNumber(),
                        'qty' => $sku->getQuantity(),
                        'status' => is_null($sku->getStatus()) ? null : $sku->getStatus()->getName(),
                        'image_url' => $image,
                        'image_alt' => $sku->getProduct()->getName(),
                        'image_title' => $sku->getProduct()->getName(),
                    ]
                ];
            }
            else
            {
                return [
                    'error' => false,
                    'sku_info' => null
                ];
            }
        }

        return null;
     }
}