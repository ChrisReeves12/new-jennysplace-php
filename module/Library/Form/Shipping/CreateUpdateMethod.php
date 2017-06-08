<?php
/**
* The CreateUpdateMethod class definition.
*
* The form used to update and create a shipping method
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Shipping;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateUpdateMethod
 * @package Library\Form\Shipping
 */
class CreateUpdateMethod extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('edit_shipping_method');

        $this->add([
            'name' => 'carrier',
            'type' => 'Select',
            'options' => [
                'label' => 'Carrier'
            ],
            'attributes' => [
                'options' => [
                    'USPS' => 'USPS',
                    'Fedex' => 'Fedex',
                    'UPS' => 'UPS',
                    'Chart' => 'Chart'
                ]
            ]
        ]);

        $this->add([
            'name' => 'name',
            'type' => 'text',
            'options' => [
                'label' => 'Name (Alias)'
            ]
        ]);

        $this->add([
            'name' => 'carrier_id',
            'type' => 'text',
            'options' => [
                'label' => 'Carrier ID'
            ]
        ]);

        $this->add([
            'name' => 'inactive',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Inactive'
            ]
        ]);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        // Not used
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter)
        {
            $inputFilter = new InputFilter();

            $inputFilter->add([
                'name' => 'carrier',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'name',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'carrier_id',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'inactive',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}