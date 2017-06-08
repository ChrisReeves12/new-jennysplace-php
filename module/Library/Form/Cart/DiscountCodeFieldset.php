<?php
/**
* The DiscountCodeFieldset class definition.
*
* Represents the discount code form on the cart page
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Cart;

use Library\Form\MSFieldset;

/**
 * Class DiscountCodeFieldset
 * @package Library\Form\Cart
 */
class DiscountCodeFieldset extends MSFieldset
{
    public function __construct()
    {
        parent::__construct('discount_code');

        $this->add([
            'name' => 'discount_code',
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'Discount Code',
                'class' => 'form-control'
            ]
        ]);
    }
}