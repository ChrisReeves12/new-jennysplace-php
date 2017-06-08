<?php
/**
* The IShippingMethodStrategy interface definition.
*
* This interface is used to define shipping method strategies
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Library\Model\Shop\ShopList\Cart;

/**
 * Interface IShippingMethodStrategy
 * @package Library\Model\Shop
 */
interface IShippingMethodStrategy
{
    /**
     * Get shipping methods and prices
     *
     * @param Cart $cart
     * @return mixed
     */
    public function get_methods(Cart $cart);
}