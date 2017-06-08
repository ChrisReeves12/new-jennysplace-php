<?php
/**
 * The CreateUpdate class definition.
 *
 * This form creates and updates discounts
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 */

namespace Library\Form\Discount;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;

/**
 * Class CreateUpdate
 * @package Library\Form\Discount
 */
class CreateUpdate extends MSForm implements InputFilterAwareInterface
{

    protected $inputFilter;

    public function __construct($name = null)
    {

        parent::__construct('create_discount');

        $this->add([
            'name'    => 'discount',
            'type'    => 'Select',
            'options' => [
                'label' => 'Discount:'
            ]
        ]);

        $this->add([
            'name' => 'discount_script',
            'type' => 'Text',
            'options' => [
                'label' => 'Discount Script'
            ]
        ]);

        $this->add([
            'name'    => 'discount_name',
            'type'    => 'Text',
            'options' => [
                'label' => 'Discount Name:'
            ]
        ]);

        $this->add([
            'name'    => 'discount_code',
            'type'    => 'Text',
            'options' => [
                'label' => 'Discount Code:'
            ]
        ]);

        $this->add([
            'name'    => 'dollar_hurdle',
            'type'    => 'Text',
            'options' => [
                'label' => 'Dollar Hurdle:'
            ]
        ]);

        $this->add([
            'name'    => 'start_date',
            'type'    => 'Text',
            'options' => [
                'label' => 'Start Date:'
            ],
            'attributes' => [
                'class' => 'ui-datepicker'
            ]
        ]);

        $this->add([
            'name'    => 'end_date',
            'type'    => 'Text',
            'options' => [
                'label' => 'End Date:'
            ],
            'attributes' => [
                'class' => 'ui-datepicker'
            ]
        ]);

        $this->add([
            'name'       => 'inactive',
            'type'       => 'Select',
            'options'    => [
                'label' => 'Is Inactive?:'
            ],
            'attributes' => [
                'options' => [
                    0 => 'No',
                    1 => 'Yes'
                ]
            ]
        ]);

        $this->add([
            'name'    => 'discount_action',
            'type'    => 'Select',
            'options' => [
                'label' => 'Discount Action:'
            ]
        ]);

        $this->add([
            'name'    => 'discount_action_info',
            'type'    => 'Hidden'
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Save Discount'
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
                'name'     => 'discount',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'discount_script',
                'required' => false,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'discount_name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'discount_code',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'dollar_hurdle',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'start_date',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'end_date',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'inactive',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'discount_action_info',
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
