<?php
/**
* The CreditCardFieldset class definition.
*
* This form takes credit card information during the order process
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Cart;

use Library\Form\MSFieldset;

/**
 * Class CreditCardFieldset
 * @package Library\Form\Cart
 */
class CreditCardFieldset extends MSFieldset
{
    public function __construct()
    {
        parent::__construct('credit_card_form');

        $this->add([
            'name' => 'card_num',
            'type' => 'text',
            'attributes' => [
                'maxlength' => '16',
                'placeholder' => 'Credit Card Number',
                'class' => 'form-control'
            ]
        ]);

        $this->add([
            'name' => 'credit_card_csrf',
            'type' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ]
        ]);

        $this->add([
            'name' => 'pay_method',
            'type' => 'hidden',
            'attributes' => [
                'value' => 'Credit/Debit',
            ]
        ]);

        $this->add([
        'name' => 'exp_month',
        'type' => 'text',
        'attributes' => [
            'style' => 'width: 74px;',
            'maxlength' => '2',
            'placeholder' => 'MM',
            'class' => 'form-control'
            ]
        ]);

        $this->add([
            'name' => 'exp_year',
            'type' => 'text',
            'attributes' => [
                'style' => 'width: 74px;',
                'maxlength' => '4',
                'placeholder' => 'YYYY',
                'class' => 'form-control'
            ]
        ]);

        $this->add([
            'name' => 'cvc',
            'type' => 'text',
            'attributes' => [
                'style' => 'width: 74px;',
                'maxlength' => '4',
                'placeholder' => 'CVC',
                'class' => 'form-control'
            ]
        ]);
    }
}