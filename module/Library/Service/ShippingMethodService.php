<?php
/**
* The ShippingMethodService class definition.
*
* This service houses functions to deal with shipping method
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Shop\Discount;
use Library\Model\Shop\IShippingMethodStrategy;
use Library\Model\Shop\ShippingMethod;
use Library\Model\Shop\ShopList\Cart;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class ShippingMethodService
 * @package Library\Service
 */
class ShippingMethodService extends AbstractService
{
    /**
     * @param Cart $cart
     *
     * @return array
     * @throws \Exception
     */
    public function get_methods(Cart $cart)
    {
        $em = EntityManagerSingleton::getInstance();
        $discount_service = $this->getServiceManager()->get('discount');

        // Check discount pricing for shipping method
        $sitewide_discount_id = Settings::get('global_discount');
        $global_discount = ($sitewide_discount_id > 0) ? $em->getReference('Library\Model\Shop\Discount', $sitewide_discount_id) : null;
        $cart_discounts = $cart->getDiscounts();

        // Run the correct shipping plugin
        $shipping_carrier_results = [];
        if ($cart->getTotalWeight() > 0)
        {
            $shipping_plugin_class_names = Settings::get('shipping_methods');
            if (!empty($shipping_plugin_class_names))
            {
                if (is_array($shipping_plugin_class_names))
                {
                    foreach ($shipping_plugin_class_names as $shipping_plugin_class_name)
                    {
                        $shipping_method_plugin = new $shipping_plugin_class_name();

                        if ($shipping_method_plugin instanceof IShippingMethodStrategy)
                        {
                            $shipping_carrier_results[$shipping_plugin_class_name] = $shipping_method_plugin->get_methods($cart);
                        } else
                        {
                            throw new \Exception("The shipping plugin being used must implement IShippingMethodStrategy");
                        }
                    }
                }
                else
                {
                    // Handle single shipping carrier option selections
                    /** @var string $shipping_plugin_class_name */
                    $shipping_plugin_class_name = $shipping_plugin_class_names;
                    $shipping_method_plugin = new $shipping_plugin_class_name();

                    if ($shipping_method_plugin instanceof IShippingMethodStrategy)
                    {
                        $shipping_carrier_results[$shipping_plugin_class_name] = $shipping_method_plugin->get_methods($cart);
                    } else
                    {
                        throw new \Exception("The shipping plugin being used must implement IShippingMethodStrategy");
                    }

                    $shipping_carrier_results[$shipping_plugin_class_name] = $shipping_method_plugin->get_methods($cart);
                }
            }

            // Query database for proper names of active shipping methods
            if (!empty($shipping_carrier_results))
            {
                foreach ($shipping_carrier_results as $key => $shipping_carrier_result)
                {
                    $carrier = $shipping_carrier_result[0]['carrier'];
                    $shipping_results = [];

                    $shipping_methods = EntityManagerSingleton::getInstance()->getRepository('Library\Model\Shop\ShippingMethod')->findBy(['carrier' => $carrier, 'inactive' => false]);
                    if (!empty($shipping_methods))
                    {
                        $shipping_dictionary = [];
                        foreach ($shipping_methods as $shipping_method)
                        {
                            $shipping_dictionary[$shipping_method->getCarrierId()] = $shipping_method->getName();
                        }
                    }

                    foreach ($shipping_carrier_result as &$shipping_method_info)
                    {
                        if (isset($shipping_dictionary[$shipping_method_info['shipping_method_id']]))
                        {
                            $discounted_price = $shipping_method_info['price'];

                            // Check if display price should be discounted with cart discounts
                            /** @var Discount $cart_discount */
                            foreach ($cart_discounts as $cart_discount)
                            {
                                if ($cart->getSubTotal() >= $cart_discount->getDollarHurdle())
                                {
                                    $cart_discount_actions = $cart_discount->getDiscountDiscountActions();
                                    foreach ($cart_discount_actions as $cart_discount_action_rel)
                                    {
                                        $cart_discount_action = $cart_discount_action_rel->getDiscountAction();
                                        if (!is_null($result = $discount_service->getDiscountShippingMethodPrice($cart_discount_action, $shipping_method_info['shipping_method_id'], $shipping_method_info['carrier'], $shipping_method_info['price'])))
                                        {
                                            $discounted_price = $result;
                                        }
                                    }
                                }
                            }

                            // Check if display price should be discounted with global discount if price wasn't discount with cart discounts
                            if ($shipping_method_info['price'] == $discounted_price)
                            {
                                if ($global_discount instanceof Discount)
                                {
                                    if ($cart->getSubTotal() >= $global_discount->getDollarHurdle())
                                    {
                                        $global_discount_actions = $global_discount->getDiscountDiscountActions();
                                        foreach ($global_discount_actions as $global_discount_action_rel)
                                        {
                                            $global_discount_action = $global_discount_action_rel->getDiscountAction();
                                            if (!is_null($result = $discount_service->getDiscountShippingMethodPrice($global_discount_action, $shipping_method_info['shipping_method_id'], $shipping_method_info['carrier'], $shipping_method_info['price'])))
                                            {
                                                $discounted_price = $result;
                                            }

                                        }
                                    }
                                }
                            }


                            $list_shipping_method = ['shipping_method_id' => $shipping_method_info['shipping_method_id'], 'name' => $shipping_dictionary[$shipping_method_info['shipping_method_id']], 'price' => number_format($discounted_price, 2, '.', ''), 'carrier' => $shipping_method_info['carrier']];
                            $shipping_results[] = $list_shipping_method;
                        }
                    }

                    $shipping_carrier_results[$key] = $shipping_results;
                }
            }
        }

        return $shipping_carrier_results;
    }

    /**
     * Saves a shipping method to the database
     *
     * @param array $data
     * @param \Library\Model\Shop\ShippingMethod $shipping_method
     *
     * @return ShippingMethod
     */
    public function save($data, $shipping_method)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        if (!($shipping_method instanceof ShippingMethod))
        {
            $shipping_method = new ShippingMethod();
        }

        // Save shipping method
        $shipping_method->setCarrier($data['carrier']);
        $shipping_method->setCarrierId($data['carrier_id']);
        $shipping_method->setInactive($data['inactive']);
        $shipping_method->setName($data['name']);
        $em->persist($shipping_method);

        return $shipping_method;
    }
}