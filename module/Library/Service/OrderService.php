<?php
/**
* The OrderService class definition.
*
* This manages the CRUD functions for orders and order elements
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Product\Product;
use Library\Model\Product\Sku;
use Library\Model\Shop\PaymentMethod;
use Library\Model\Shop\ProductReturn;
use Library\Model\Shop\ShippingMethod;
use Library\Model\Shop\ShopList\Cart;
use Library\Model\Shop\ShopList\Order;
use Library\Model\Shop\ShopListElement;
use Library\Model\User\Address;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class OrderService
 * @package Library\Service
 */
class OrderService extends AbstractService
{
    /**
     * Creates or updates a customer's order in the database
     *
     * @param array $data
     * @param Order $order
     *
     * @return Order
     * @throws \Exception
     */
    public function save($data, $order)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $session_service = $this->getServiceManager()->get('session');
        $payment_service = $this->getServiceManager()->get('payment');
        $user_service = $this->getServiceManager()->get('user');

        // Defaults
        $pay_method = $pay_info = $payment_method = $saved_cart = $session_pay_data = null;
        $inventory_check = Settings::get('inventory_check');

        // Check if we are updating or creating a new order
        if (!($order instanceof Order))
        {
            $user = $user_service->getIdentity();
            if (!($user instanceof User))
            {
                throw new \Exception("You must be logged in to complete an order. Please log in.");
            }

            $saved_cart = $user->getSavedCart();
            if (!($saved_cart instanceof Cart))
            {
                throw new \Exception("The shopping associated with your account has been deleted. Please place your order again.");
            }

            // Validate if any of the items in the order have been labeled as disabled or out of stock
            $cart_line_items = $saved_cart->getShopListElements();
            if ($cart_line_items->count() > 0)
            {
                foreach ($cart_line_items as $cart_line_item)
                {
                    $status_name = $cart_line_item->getSku()->getRealStatus()->getName();
                    if ($status_name == "Out Of Stock" || $status_name == "Disabled")
                    {
                        throw new \Exception($cart_line_item->getSku()->getProduct()->getName() . " is out of stock or disabled.");
                    }

                    // Check if quantity is higher than the stock
                    if ($inventory_check == "1")
                    {
                        $on_hands = $cart_line_item->getSku()->getQuantity();
                        $qty = $cart_line_item->getQuantity();

                        if ($qty > $on_hands)
                        {
                            throw new \Exception("There are only " . $on_hands .  " of " . $cart_line_item->getSku()->getProduct()->getName() . " in our inventory at this time.\nPlease adjust the quantity of that line item please.");
                        }
                    }
                }
            }
            else
            {
                throw new \Exception("Your cart must have items in it to be able to place an order.");
            }

            $order = new Order();

            // Get payment information
            if (!is_null($data))
            {
                $pay_method = $data['pay_info']['pay_method'];
            }
            else
            {
                // If this is null, we may be returning from a payment gateway
                $session_pay_data = $session_service->getContainer('cart')['pay_data'];
                if (is_null($session_pay_data))
                    throw new \Exception("No data entered to save order");

                $pay_method = $session_pay_data['pay_method'];
            }

            if (!is_null($data))
                $pay_info = $data['pay_info'];
            else
                $pay_info = $session_pay_data;

            // Save store credit and deduct from user
            $store_credit = $saved_cart->getStoreCredit();
            $user = $saved_cart->getUser();
            $order->setStoreCredit($store_credit);
            $user->deductStoreCredit($store_credit);
        }

        // Update information from data if data is set
        if (!is_null($data))
        {
            // Update billing address
            $billing_address = $order->getBillingAddress();
            if (!($billing_address instanceof Address))
            {
                $billing_address = new Address();
                $em->persist($billing_address);
                $order->setBillingAddress($billing_address);

                if (!is_null($saved_cart)) $saved_cart->setBillingAddress($billing_address);
            }

            $billing_address->setCompany($data['billing_address']['company']);
            $billing_address->setFirstName($data['billing_address']['first_name']);
            $billing_address->setLastName($data['billing_address']['last_name']);
            $billing_address->setEmail($data['billing_address']['email']);
            $billing_address->setLine1($data['billing_address']['line_1']);
            $billing_address->setLine2($data['billing_address']['line_2']);
            $billing_address->setCity($data['billing_address']['city']);
            $billing_address->setState($data['billing_address']['state']);
            $billing_address->setZipcode($data['billing_address']['zipcode']);

            if (!empty($data['billing_address']['phone'])) $billing_address->setPhone($data['billing_address']['phone']);

            // Update shipping address
            $shipping_address = $order->getShippingAddress();
            if (!($shipping_address instanceof Address))
            {
                $shipping_address = new Address();
                $em->persist($shipping_address);
                $order->setShippingAddress($shipping_address);

                if (!is_null($saved_cart)) $saved_cart->setShippingAddress($shipping_address);
            }

            $shipping_address->setCompany($data['shipping_address']['company']);
            $shipping_address->setFirstName($data['shipping_address']['first_name']);
            $shipping_address->setLastName($data['shipping_address']['last_name']);
            $shipping_address->setEmail($data['shipping_address']['email']);
            $shipping_address->setLine1($data['shipping_address']['line_1']);
            $shipping_address->setLine2($data['shipping_address']['line_2']);
            $shipping_address->setCity($data['shipping_address']['city']);
            $shipping_address->setState($data['shipping_address']['state']);
            $shipping_address->setZipcode($data['shipping_address']['zipcode']);

            if (!empty($data['shipping_address']['phone'])) $shipping_address->setPhone($data['shipping_address']['phone']);
        }
        else
        {
            // Get addresses from saved cart if no data is present
            $shipping_address = $saved_cart->getShippingAddress();
            $billing_address = $saved_cart->getBillingAddress();
            $order->setShippingAddress($shipping_address);
            $order->setBillingAddress($billing_address);
        }

        // Popuplate sale info map from user cart for new orders being placed
        if (is_null($data) || empty($data['sale_info']))
        {
            // Save addresses from data to cart so we can use them later
            if (!is_null($data))
            {
                $em->flush($shipping_address);
                $em->flush($billing_address);
            }

            // Authorize payment
            if (!isset($session_pay_data['pay_data']['order_number']))
                $order_number = $this->getUniqueOrderNumber();
            else
                $order_number = $session_pay_data['pay_data']['order_number'];

            // If the store credit pays for the whole order, make 'Store Credit' the payment method
            if ($order->getStoreCredit() > 0 && $saved_cart->getTotal() == 0)
            {
                $data['sale_info'] = [];
                $data['sale_info'] = [
                    'auth_code' => 'N/A',
                    'trans_id' => 'N/A',
                    'notes' => 'Used $' . $order->getStoreCredit() . ' of online store credit.',
                    'payment_method' => 'Store Credit/Voucher',
                    'tax' => $saved_cart->getTax(),
                    'shipping_cost' => $saved_cart->getCurrentShippingCost(),
                    'total_discount' => $saved_cart->getDiscountAmount(),
                    'status' => 'Pending',
                    'shipping_method' => $saved_cart->getShippingMethod()
                ];

                $payment_method = $em->getRepository('Library\Model\Shop\PaymentMethod')->findOneById(3);
            }
            else
            {
                /** @type PaymentMethod $payment_method */
                $payment_method = $em->getRepository('Library\Model\Shop\PaymentMethod')->findOneBy(['name' => $pay_method, 'is_supported' => true]);
                $transaction_info = $payment_service->process_payment($payment_method, ['order_number' => $order_number, 'cart' => $saved_cart, 'pay_info' => $pay_info]);

                // Check if payment was successful
                if (!$transaction_info['success'])
                {
                    return $transaction_info;
                }

                $data['sale_info'] = [];
                $data['sale_info'] = [
                    'auth_code' => $transaction_info['auth_code'],
                    'trans_id' => $transaction_info['trans_id'],
                    'notes' => $transaction_info['pay_type'] . ' - Card #: ' . $transaction_info['card_num'],
                    'payment_method' => $transaction_info['pay_type'],
                    'tax' => $transaction_info['tax'],
                    'shipping_cost' => $saved_cart->getCurrentShippingCost(),
                    'total_discount' => $saved_cart->getDiscountAmount(),
                    'status' => 'Pending',
                    'shipping_method' => $saved_cart->getShippingMethod()
                ];
            }

            // Update sales information for new orders
            $order->setAuthCode($data['sale_info']['auth_code']);
            $order->setNotes($data['sale_info']['notes']);
            $order->setOrderNumber($order_number);
            $order->setUser($saved_cart->getUser());
            $order->setTransactionId($data['sale_info']['trans_id']);
            $order->setIpAddress($user_service->getClientIp());
            $order->setPaymentMethod($payment_method);
        }

        // Update sales information for all orders
        $order->setTax($data['sale_info']['tax']);
        $order->setTrackingNumber($data['sale_info']['tracking_number']);
        $order->setShippingCost($data['sale_info']['shipping_cost']);
        $order->setDiscountAmount($data['sale_info']['total_discount']);
        $order->setStatus($data['sale_info']['status']);
        $order->setDateModified();

        if (!empty($data['sale_info']['shipping_method']))
        {
            if (!($data['sale_info']['shipping_method'] instanceof ShippingMethod))
                $shipping_method = $em->getRepository('Library\Model\Shop\ShippingMethod')->findOneById($data['sale_info']['shipping_method']);
            else
                $shipping_method = $data['sale_info']['shipping_method'];
        }
        else
        {
            throw new \Exception("A shipping method must be specified to complete order.");
        }


        if (!($shipping_method instanceof ShippingMethod))
        {
            throw new \Exception("The shipping method being used on this order is not registered in the database.");
        }

        $order->setShippingMethod($shipping_method);

        // On new orders, change cart line items to order line items
        if (empty($data['line_items']))
        {
            if (!isset($user))
                throw new \Exception("An error occured while saving order line items. User is not logged in or user cannot be found.");

            if (!isset($saved_cart))
                throw new \Exception("The user's shopping cart could not be retrieved when trying to save line items. The cart may have been removed from the database.");

            $line_items = $saved_cart->getShopListElements();
            $data['line_items'] = [];

            if (!empty($line_items))
            {
                foreach ($line_items as $line_item)
                {
                    $data['line_items'][$line_item->getId()] = [
                        'quantity' => $line_item->getQuantity(),
                        'price' => $line_item->getPrice(),
                        'tax' => $line_item->getTax(),
                        'name' => $line_item->getName(),
                        'weight' => $line_item->getWeight(),
                        'sku' => $line_item->getSku(),
                        'number' => $line_item->getNumber(),
                        'image' => $line_item->getImage()
                    ];
                }
            }
            else
            {
                throw new \Exception("The order being placed must have at least one line item.");
            }
        }

        // Save each line item if modifying an order
        foreach ($data['line_items'] as $line_item_id => $line_item)
        {
            // For existing orders, get the line item from database
            if (!is_null($order->getId()))
            {
                $line_item_obj = $em->getRepository('Library\Model\Shop\ShopListElement')->findOneById($line_item_id);
            }
            else
            {
                // Create new line object for new orders
                $line_item_obj = new ShopListElement();
                $line_item_obj->setShopList($order);
                $line_item_obj->setSku($line_item['sku']);
                $line_item_obj->setNumber($line_item['number']);
                $line_item_obj->setImage($line_item['image']);
                $order->addShopListElement($line_item_obj);
                $em->persist($line_item_obj);

                // Update sku inventory if that setting is on
                if ($inventory_check == "1")
                {
                    $line_item['sku']->setQuantity($line_item['sku']->getQuantity() - $line_item['quantity']);
                    $em->persist($line_item['sku']);
                }
            }

            if (!($line_item_obj instanceof ShopListElement))
            {
                throw new \Exception("Line item #" . $line_item_id . " no longer exists in the database and cannot be modified.");
            }

            $line_item_obj->setQuantity($line_item['quantity']);
            $line_item_obj->setPrice($line_item['price']);
            $line_item_obj->setTax($line_item['tax']);
            $line_item_obj->setName($line_item['name']);
            $line_item_obj->setWeight($line_item['weight']);
        }

        // Persist new order
        if (is_null($order->getId()))
        {
            $em->persist($order);
        }

        return $order;
    }

    /**
     * Adds a line item to the order.
     *
     * @param int $order_id
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function addLineItem($order_id, $data)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $product = $em->getRepository('Library\Model\Product\Product')->findOneById($data['id']);
        if (!($product instanceof Product))
        {
            throw new \Exception("The product being adding is not located in the database.");
        }

        // Locate sku to add
        if (!empty($data['option_values']))
        {
            $sku = $em->getRepository('Library\Model\Product\Sku')->findOneByOptions($product, $data['option_values']);
        }
        else
        {
            $sku = $product->getDefaultSku();
        }

        if (!($sku instanceof Sku))
        {
            throw new \Exception("A sku could not be found from the options");
        }

        // Locate order
        $order = $em->getRepository('Library\Model\Shop\ShopList\Order')->findOneById($order_id);
        if ($order instanceof Order)
        {
            // Find the sku and add it to the order
            $shop_list_element = new ShopListElement();
            $shop_list_element->convertSkuToElement($sku, $data['product_qty']);
            $shop_list_element->setShopList($order);
            $em->persist($shop_list_element);
            $order->setDateModified();
        }
        else
        {
            throw new \Exception("The order id passed in cannot be found in the database.");
        }

        return [$order, $shop_list_element];
    }

    /**
     * Gets a global, incrementing number to create order numbers from
     *
     * @return int
     */
    public function getUniqueOrderNumber()
    {
        $uid =  (int) file_get_contents(getcwd() . '/data/cache/uid.id');
        $uid++;
        file_put_contents(getcwd() . '/data/cache/uid.id', $uid);

        return $uid;
    }

    /**
     * Delete shop list elements
     *
     * @param $ids[]
     * @param \Library\Model\Shop\ShopList\Order $order
     */
    public function deleteShopListElements($ids, $order)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $order->setDateModified();

        $shop_list_elements = $order->getShopListElements();

        foreach ($shop_list_elements as &$shop_list_element)
        {
            if (in_array($shop_list_element->getId(), $ids))
            {
                $em->remove($shop_list_element);
                $shop_list_elements->removeElement($shop_list_element);
            }
        }
    }

    /**
     * Save product return
     *
     * @param array $data
     * @param ProductReturn $return
     * @param ShopListElement $shop_list_element
     * @param User $user
     */
    public function saveOrderReturn($data, ProductReturn $return = null, ShopListElement $shop_list_element = null, User $user = null)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        if (!($return instanceof ProductReturn))
        {
            $return = new ProductReturn();
            $em->persist($return);
        }

        // Save info
        if (isset($data['admin_message']))
        {
            $return->setAdminMessage($data['admin_message']);
        }
        else
        {
            $return->setAdminMessage('');
        }

        if (isset($data['customer_message']))
        {
            $return->setCustomerMessage($data['customer_message']);
        }
        else
        {
            $return->setCustomerMessage($data['message']);
        }

        if (isset($data['status']))
        {
            $return->setStatus($data['status']);
        }
        else
        {
            $return->setStatus('pending');
        }

        // Set associated shop list element
        if ($shop_list_element instanceof ShopListElement)
        {
            $return->setShopListElement($shop_list_element);
            $return->setUser($user);
        }
    }
}