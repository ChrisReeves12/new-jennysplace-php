<?php
/**
* The Action class definition.
*
* This form handles the administration of discount actions
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Discount;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class Action
 * @package Library\Form\Discount
 */
class Action extends MSForm
{
    protected $inputFilter;

    public function __construct($name = null)
    {

        parent::__construct('discount_action_form');

        $this->add([
            'name'    => 'discountaction',
            'type'    => 'Select',
            'options' => [
                'label' => 'Discount Action:'
            ]
        ]);

        $this->add([
            'name'    => 'action_name',
            'type'    => 'Text',
            'options' => [
                'label' => 'Discount Action Name:'
            ]
        ]);

        $this->add([
            'name'    => 'shipping_discount',
            'type'    => 'Text',
            'options' => [
                'label' => 'Shipping Discount:'
            ]
        ]);

        $this->add([
            'name'       => 'shipping_discount_type',
            'type'       => 'Select',
            'options'    => [
                'label' => 'Discount Type:'
            ],
            'attributes' => [
                'options' => [
                    'rate'    => 'Rate',
                    'percent' => 'Percent',
                    'dollar'  => 'Dollar'
                ]
            ]
        ]);

        $this->add([
            'name'    => 'shipping_method',
            'type'    => 'Select',
            'options' => [
                'label' => 'Shipping Method:'
            ]
        ]);

        $this->add([
            'name'    => 'product_total_discount',
            'type'    => 'Text',
            'options' => [
                'label' => 'Product Total Discount:'
            ]
        ]);

        $this->add([
            'name'       => 'product_discount_type',
            'type'       => 'Select',
            'options'    => [
                'label' => 'Discount Type:'
            ],
            'attributes' => [
                'options' => [
                    'percent' => 'Percent',
                    'dollar'  => 'Dollar'
                ]
            ]
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Save Discount Action'
            ]
        ]);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception('Not used');
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter)
        {

            $inputFilter = new InputFilter();

            $inputFilter->add([
                'name'     => 'discountaction',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'action_name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'shipping_discount',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'shipping_discount_type',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'shipping_method',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'product_discount_type',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'product_total_discount',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}