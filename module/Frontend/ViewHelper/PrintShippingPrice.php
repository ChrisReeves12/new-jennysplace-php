<?php
/**
 * The PrintShippingPrice class definition.
 *
 * Prints the shipping price correctly
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Frontend\ViewHelper;

use Zend\Form\View\Helper\AbstractHelper;

/**
 * Class PrintShippingPrice
 * @package Frontend\ViewHelper
 */
class PrintShippingPrice extends AbstractHelper
{
    /**
     * @param float $price
     * @return string
     */
    public function __invoke($price)
    {
        return ($price == 0) ? "Free" : "{$price}";
    }
}