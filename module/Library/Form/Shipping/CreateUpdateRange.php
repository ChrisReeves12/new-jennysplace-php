<?php
/**
* The CreateUpdate class definition.
*
* The description of the class
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Shipping;

use Library\Form\MSForm;
use Library\Service\DB\EntityManagerSingleton;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateUpdate
 * @package Library\Form\Shipping
 */
class CreateUpdateRange extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('create_update_shipping_range');

        // Get shipping methods
        $em = EntityManagerSingleton::getInstance();
        $shipping_methods = $em->getRepository('Library\Model\Shop\ShippingMethod')->findBy(['inactive' => false, 'carrier' => 'Chart']);
        $shipping_method_data = [];
        if (count($shipping_methods) > 0)
        {
            foreach ($shipping_methods as $shipping_method)
            {
                $shipping_method_data[$shipping_method->getId()] = $shipping_method->getName();
            }
        }


        $this->add([
            'name' => 'shipping_method',
            'type' => 'Select',
            'options' => [
                'label' => 'Shipping Method'
            ],
            'attributes' => [
                'options' => $shipping_method_data
            ]
        ]);

        $this->add([
            'name' => 'price',
            'type' => 'text',
            'options' => [
                'label' => 'Price'
            ]
        ]);

        $this->add([
            'name' => 'low_value',
            'type' => 'text',
            'options' => [
                'label' => 'Low Value'
            ]
        ]);

        $this->add([
            'name' => 'high_value',
            'type' => 'text',
            'options' => [
                'label' => 'High Value'
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
                'name' => 'shipping_method',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'price',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'low_value',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'high_value',
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