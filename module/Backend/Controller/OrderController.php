<?php
/**
* The OrderController class definition.
*
* This controller handles the displaying, updating and deleting of orders and order elements
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Model\Media\Image;
use Library\Model\Shop\ShopList\Order;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;
use Zend\View\Model\JsonModel;

/**
 * Class OrderController
 * @package Backend\Controller
 */
class OrderController extends JPController
{
    protected $order_id;
    protected $order;

    /**
     * Displays a single order for viewing and editing
     * @return array
     * @throws \Exception
     */
    public function singleAction()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $this->order_id = isset($_GET['id']) ? $_GET['id'] : null;

        // Load order if ID is passed in
        if (!empty($this->order_id))
        {
            $this->order = $em->getRepository('Library\Model\Shop\ShopList\Order')->findOneById($this->order_id);

            if (!($this->order instanceof Order))
            {
                throw new \Exception("The order id passed in cannot be matched to an order in the database.");
            }
        }
        else
        {
            // No order id passed in
            $this->getResponse()->setStatusCode(404);
            return [];
        }

        // Handle incoming posts
        $response = $this->_handle_post();
        if (!empty($response))
        {
            return new JsonModel($response);
        }

        // Get shipping methods
        $shipping_methods = $em->getRepository('Library\Model\Shop\ShippingMethod')->findBy(['inactive' => false]);

        // Attach javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/order.js');

        // Create dictionary of shipping methods for display on page
        if (count($shipping_methods) > 0)
        {
            $shipping_method_dictionary = [];
            foreach ($shipping_methods as $shipping_method)
            {
                $shipping_method_dictionary[] = [$shipping_method->getId(), $shipping_method->getName()];
            }

            $shipping_methods_json = json_encode($shipping_method_dictionary);
        }
        else
        {
            $shipping_methods_json = "{ }";
        }

        // Create json data for order line items
        $order_lines = $this->order->getShopListElements();
        if (count($order_lines) > 0)
        {
            $line_items = [];
            foreach ($order_lines as $order_line)
            {
                $image_root = \Library\Service\Settings::get('image_path');

                if ($order_line->getImage() instanceof Image)
                    $product_photo = $image_root . "/product_images/".$order_line->getImage()->getUrl();
                else
                    $product_photo = $image_root . '/layout_images/no_photo.jpg';

                $line_item = [
                    'id' => $order_line->getId(),
                    'number' => $order_line->getNumber(),
                    'quantity' => $order_line->getQuantity(),
                    'tax' => $order_line->getTax(),
                    'price' => $order_line->getPrice(),
                    'name' => $order_line->getName(),
                    'weight' => $order_line->getWeight(),
                    'image' => $product_photo,
                    'total' => $order_line->getTotal(),
                    'attributes' => []
                ];

                $soovs = $order_line->getSku()->getSkuOptionOptionValues();
                if (count($soovs) > 0)
                {
                    foreach ($soovs as $soov)
                    {
                        $option_value_rel = $soov->getOptionOptionValue();
                        $line_item['attributes'][] = [$option_value_rel->getOption()->getName(), $option_value_rel->getOptionValue()->getName()];
                    }
                }

                $line_items[] = $line_item;
            }

            $order_lines_json = json_encode($line_items);
        }
        else
        {
            $order_lines_json = "{ }";
        }


        return ['order' => $this->order, 'shipping_methods_json' => $shipping_methods_json, 'order_lines_json' => $order_lines_json];
    }

    /**
     * Handle incoming post requests
     * @return array
     */
    private function _handle_post()
    {
        $order_service = $this->getServiceLocator()->get('order');

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            $task = $data['task'];
            unset($data['task']);

            // Handle different tasks that may be coming in
            switch ($task)
            {
                case 'save_order':

                    $this->order = $order_service->save($data['order_info'], $this->order);
                    EntityManagerSingleton::getInstance()->flush();
                    return ['error' => false];
                    break;

                case 'delete_order_lines':

                    $order_service->deleteShopListElements($data['order_line_ids'], $this->order);

                    EntityManagerSingleton::getInstance()->flush();
                    return ['error' => false];
                    break;

                case 'product_search':

                    $em = EntityManagerSingleton::getInstance();
                    $products = $em->getRepository('Library\Model\Product\Product')->findByKeywords($_POST['value'], 0, 12);
                    $image_root = Settings::get('image_path');

                    $product_array = [];
                    foreach ($products as $product)
                    {
                        // Get correct product photo
                        if ($product->getDefaultImage() instanceof Image)
                            $product_photo = $image_root . '/product_images/' . $product->getDefaultImage()->getUrl();
                        else
                            $product_photo = $image_root . '/layout_images/no_photo.jpg';

                        $product_info = [
                            'id' => $product->getId(),
                            'label' => $product->getName(),
                            'base_price' => $product->getBasePrice(),
                            'discount_price' => $product->getDiscountPrice(),
                            'product_code' => $product->getProductCode(),
                            'img' => $product_photo,
                            'href' => $product->getPage()->getUrlHandle(),
                            'product_desc' => $product->getDescription(),
                            'description' => 'Product Code: ' . $product->getProductCode()
                        ];

                        // Add information on options
                        $skus = $product->getSkus();
                        $options = $em->getRepository('Library\Model\Product\Option')->findByProduct($product->getId());

                        // Get available values
                        $option_value_map = [];

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
                                                $option_value_map[$option->getId()]['name'] = $option->getName();
                                                $option_value_map[$option->getId()]['values'][$sku_option_value->getId()] = $sku_option_value->getName();
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $product_info['option_map'] = $option_value_map;
                        $product_array[] = $product_info;
                    }

                    return ['products' => $product_array];
                    break;

                case 'add_product':

                    $em = EntityManagerSingleton::getInstance();
                    $order_id = $_GET['id'] ?? null;

                    // Locate the product
                    list($order, $shop_list_element) = $order_service->addLineItem($order_id, $_POST['data']);

                    $em->flush();

                    // Create updated data to pass
                    $updated_data = [
                        'status' => 'show',
                        'sub_total' => $order->getSubTotal(),
                        'tax' => $order->getTax(),
                        'shipping_cost' => $order->getShippingCost(),
                        'tracking_number' => $order->getTrackingNumber(),
                        'total_discount' => $order->getDiscountAmount(),
                        'store_credit' => $order->getStoreCredit(),
                        'total' => $order->getTotal(),
                        'total_weight' => $order->getTotalWeight(),
                        'date_shipped' => !empty($order->getShippingDate()) ? $order->getShippingDate()->format('m/d/Y') : ''
                    ];

                    // Get correct product photo
                    $image_root = Settings::get('image_path');

                    if ($shop_list_element->getImage() instanceof Image)
                        $product_photo = $image_root . "/product_images/".$shop_list_element->getImage()->getUrl();
                    else
                        $product_photo = $image_root . '/layout_images/no_photo.jpg';

                    // Get product information
                    $product_data = [
                        'id' => $shop_list_element->getId(),
                        'number' => $shop_list_element->getNumber(),
                        'quantity' => $shop_list_element->getQuantity(),
                        'tax' => $shop_list_element->getTax(),
                        'price' => $shop_list_element->getPrice(),
                        'name' => $shop_list_element->getName(),
                        'weight' => $shop_list_element->getWeight(),
                        'image' => $product_photo,
                        'total' => $shop_list_element->getTotal(),
                        'attributes' => []
                    ];

                    $soovs = $shop_list_element->getSku()->getSkuOptionOptionValues();
                    if (count($soovs) > 0)
                    {
                        foreach ($soovs as $soov)
                        {
                            $option_value_rel = $soov->getOptionOptionValue();
                            $product_data['attributes'][] = [$option_value_rel->getOption()->getName(), $option_value_rel->getOptionValue()->getName()];
                        }
                    }

                    return ['error' => false, 'order_data' => $updated_data, 'product_data' => $product_data];
            }
        }
    }

    /**
     * Shows the fufillment print for the order
     */
    public function fufillmentAction()
    {
        // Set layout template
        $this->layout()->setTemplate('backend/fufillment_layout');
        $this->order_id = isset($_GET['id']) ? $_GET['id'] : null;
        $order = $this->_get_order($this->order_id);

        // Get store information
        $this->layout()->setVariables(['store_settings' => Settings::getAll()]);

        // Set layout information
        $this->layout()->setVariable('order_number', $order->getOrderNumber());

        return ['order' => $order];
    }

    /**
     * Shows the customer's invoice for the order
     */
    public function invoiceAction()
    {
        // Set layout template
        $this->layout()->setTemplate('backend/invoice_layout');
        $this->order_id = isset($_GET['id']) ? $_GET['id'] : null;
        $order = $this->_get_order($this->order_id);

        // Get store information
        $this->layout()->setVariables(['store_settings' => Settings::getAll()]);

        // Logo filename
        $image_service = $this->getServiceLocator()->get('image');
        $store_settings = Settings::getAll();
        $logo_file = $image_service->getFileNameFromTempName($store_settings['store_logo']['tmp_name']  );

        return ['order' => $order, 'logo_file' => $logo_file];
    }

    /**
     * Gets order to send to view
     * @param int $id
     *
     * @return Order
     * @throws \Exception
     */
    private function _get_order($id)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $order = $em->getRepository('Library\Model\Shop\ShopList\Order')->findOneBy(['order_number' => $id]);
        if (!$order instanceof Order)
        {
            throw new \Exception("The order cannot be found");
        }

        // Get store information
        $this->layout()->setVariables(['store_settings' => Settings::getAll()]);

        return $order;
    }
}