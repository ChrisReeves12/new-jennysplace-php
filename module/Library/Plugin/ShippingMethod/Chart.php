<?php
/**
* The Chart class definition.
*
* The chart strategy for getting shipping methods and rates
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Plugin\ShippingMethod;

use Library\Model\Shop\IShippingMethodStrategy;
use Library\Model\Shop\ShopList\Cart;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;

/**
 * Class Chart
 * @package Library\Plugin\ShippingMethod
 */
class Chart implements IShippingMethodStrategy
{
    /**
     * @param Cart $cart
     *
     * @return array|void
     * @throws \Exception
     */
    public function get_methods(Cart $cart)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get the value to calculate the shipping
        $method = Settings::get('chart_shipping_calculation_method');
        switch ($method)
        {
            case 'weight':
                $value = $cart->getTotalWeight();
                break;

            case 'sub-total':
                $value = $cart->getSubTotal();
                break;

            default:
                $value = $cart->getSubTotal();
                break;
        }

        // Find shipping methods based on weight
        $qb = $em->createQueryBuilder();
        $qb->select('sr')->from('Library\Model\Shop\ShippingRange', 'sr');
        $qb->innerJoin('Library\Model\Shop\ShippingMethod', 'sm', 'WITH', 'sm = sr.shipping_method');
        $qb->where('sr.low_value < :value')->andWhere('sr.high_value > :value')->andWhere('sm.inactive = 0');
        $qb->setParameters(['value' => $value]);
        $result = $qb->getQuery()->getResult();

        // Get shipping methods and collect them in array
        $shipping_rates = [];

        if (!empty($result))
        {
            foreach ($result as $shipping_range)
            {
                $shipping_rates[] = [
                    'shipping_method_id' => $shipping_range->getShippingMethod()->getCarrierId(),
                    'name' => $shipping_range->getShippingMethod()->getName(),
                    'price' => $shipping_range->getPrice(),
                    'carrier' => 'Chart'
                ];
            }
        }
        else
        {
            // No methods found in range for the given value
            $shipping_range[] = [
                'shipping_method_id' => 3,
                'name'               => 'Standard Shipping (Shipping price will be quoted after processing due to order size)',
                'price'              => '0.00',
                'carrier'            => 'Chart'
            ];
        }

        return $shipping_rates;
    }
}