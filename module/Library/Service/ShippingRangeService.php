<?php
/**
* The ShippingRangeService class definition.
*
* Various shipping range services
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Shop\ShippingRange;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class ShippingRangeService
 * @package Library\Service
 */
class ShippingRangeService extends AbstractService
{
    /**
     * @param array $data
     * @param ShippingRange $shipping_range
     *
     * @return ShippingRange
     */
    public function save($data, $shipping_range)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        if (!($shipping_range instanceof ShippingRange))
        {
            $shipping_range = new ShippingRange();
        }

        // Save info
        $shipping_method = $em->getRepository('Library\Model\Shop\ShippingMethod')->findOneById($data['shipping_method']);
        $shipping_range->setHighValue($data['high_value']);
        $shipping_range->setLowValue($data['low_value']);
        $shipping_range->setPrice($data['price']);
        $shipping_range->setShippingMethod($shipping_method);

        $em->persist($shipping_range);
        return $shipping_range;
    }
}