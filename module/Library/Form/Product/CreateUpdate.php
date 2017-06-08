<?php
/**
* The CreateUpdate class definition.
*
* This form represents the creating and editing of products
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Product;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateUpdate
 * @package Library\Form\Product
 */
class CreateUpdate extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct($name = null)
    {
        parent::__construct('create_product');

        $this->add([
            'name'    => 'name',
            'type'    => 'Text',
            'options' => [
                'label' => 'Product Name'
            ]
        ]);

        $this->add([
            'name'    => 'handle',
            'type'    => 'Text',
            'options' => [
                'label' => 'Product Handle (Leave blank and one will be created for you)'
            ]
        ]);

        $this->add([
            'name' => 'show_more_caption',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Show \'More Selections\' Caption'
            ]
        ]);

        $this->add([
            'name'    => 'date_added',
            'type'    => 'Text',
            'options' => [
                'label' => 'Date Added'
            ],
            'attributes' => [
                'class' => 'ui-datepicker'
            ]
        ]);

        $this->add([
            'name'       => 'category',
            'type'       => 'Select',
            'options'    => [
                'label' => 'Select Category'
            ],
            'attributes' => [
                'style' => 'display: inline-block;'
            ]
        ]);

        $this->add([
            'name'       => 'add_category',
            'type'       => 'Button',
            'options'    => [
                'label' => 'Add Category'
            ]
        ]);

        $this->add([
            'name' => 'category_list',
            'type' => 'Hidden'
        ]);

        $this->add([
            'name' => 'theme_list',
            'type' => 'Hidden'
        ]);

        $this->add([
            'name' => 'category_list_contents',
            'type' => 'Hidden'
        ]);

        $this->add([
            'name' => 'extra_images_contents',
            'type' => 'Hidden'
        ]);

        $this->add([
            'name' => 'skus_contents',
            'type' => 'Hidden'
        ]);

        $this->add([
            'name' => 'custom_attributes_group_contents',
            'type' => 'Hidden'
        ]);

        $this->add([
            'name'    => 'default_image',
            'type'    => 'File',
            'options' => [
                'label' => 'Default Photo'
            ]
        ]);

        $this->add([
            'name' => 'default_image_id',
            'type' => 'Hidden'
        ]);

        $this->add([
            'name'    => 'meta_description',
            'type'    => 'TextArea',
            'options' => [
                'label' => 'SEO Meta-Description'
            ]
        ]);

        $this->add([
            'name'    => 'keywords',
            'type'    => 'TextArea',
            'options' => [
                'label' => 'SEO Meta-Keywords'
            ]
        ]);

        $this->add([
            'name'    => 'product_code',
            'type'    => 'Text',
            'options' => [
                'label' => 'Model ID'
            ]
        ]);

        $this->add([
            'name'    => 'base_price',
            'type'    => 'Text',
            'options' => [
                'label' => 'Base Price'
            ]
        ]);

        $this->add([
            'name'    => 'discount_price',
            'type'    => 'Text',
            'options' => [
                'label' => 'Discount Rate'
            ]
        ]);

        $this->add([
            'name'    => 'tax',
            'type'    => 'Text',
            'options' => [
                'label' => 'Tax'
            ]
        ]);

        $this->add([
            'name'    => 'base_weight',
            'type'    => 'Text',
            'options' => [
                'label' => 'Base Weight (lbs.)'
            ]
        ]);

        //TODO: This is depricated and will soon be killed
        $this->add([
            'name'    => 'sort_order',
            'type'    => 'Hidden',
            'options' => [
                'label' => 'Sort Order'
            ]
        ]);

        $this->add([
            'name'    => 'quantity',
            'type'    => 'Text',
            'options' => [
                'label' => 'Quantity'
            ]
        ]);

        $this->add([
            'name'    => 'status_override',
            'type'    => 'Select',
            'options' => [
                'label' => 'Status Override'
            ]
        ]);

        $this->add([
            'name'    => 'status',
            'type'    => 'Select',
            'options' => [
                'label' => 'Status'
            ],
            'attributes' => [
                'disabled' => 'disabled'
            ]
        ]);

        $this->add([
            'name'    => 'description',
            'type'    => 'TextArea',
            'options' => [
                'label' => 'Description'
            ]
        ]);

        $this->add([
            'name' => 'product_id',
            'type' => 'Hidden',
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => 'Save Product'
            ]
        ]);
    }

    public function getInputFilter()
    {

        if (!$this->inputFilter)
        {

            $inputFilter = new InputFilter();

            $inputFilter->add([
                'name'     => 'name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'handle',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'show_more_caption',
                'required' => false
            ]);

            $inputFilter->add([
                'name'     => 'discount_price',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'meta_description',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'date_added',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'keywords',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'base_price',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'category_list',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'theme_list',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'custom_attributes_group_contents',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'extra_images_contents',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'skus_contents',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'tax',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'base_weight',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'sort_order',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'quantity',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'product_code',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'status_override',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'default_image_id',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'description',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'product_id',
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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }
}