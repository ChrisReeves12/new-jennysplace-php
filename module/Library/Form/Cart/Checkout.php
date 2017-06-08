<?php
/**
* The Checkout class definition.
*
* The checkout form
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Cart;

use Library\Form\MSForm;

/**
 * Class Checkout
 * @package Library\Form\Cart
 */
class Checkout extends MSForm
{
    public function __construct()
    {
        parent::__construct('checkout_form');

        $this->add([
            'name' => 'billing_address',
            'type' => 'Library\Form\User\AddressInfoFieldset'
        ]);

        $this->add([
            'name' => 'checkout_csrf',
            'type' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ]
        ]);

        $this->add([
            'name' => 'shipping_address',
            'type' => 'Library\Form\User\AddressInfoFieldset'
        ]);

        $this->add([
            'name' => 'pay_info',
            'type' => 'Library\Form\Cart\CreditCardFieldset'
        ]);

        $this->add([
            'name' => 'discount_code',
            'type' => 'Library\Form\Cart\DiscountCodeFieldset'
        ]);

        // Set filter specs for credit card form
        $credit_card_filter = [
            'card_num' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ],

            'pay_method' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ],

            'exp_month' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ],

            'exp_year' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ],

            'cvc' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]
        ];

        // Set filter specs for discount code form
        $discount_code_filter = [
            'discount_code' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]
        ];

        $this->setupFilterSpecs('discount_code', $discount_code_filter);
        $this->setupFilterSpecs('pay_info', $credit_card_filter);

    }
}