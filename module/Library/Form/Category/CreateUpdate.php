<?php
/**
* The CreateUpdate class definition.
*
* This form updates and creates categories
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Category;

use Library\Form\MSForm;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateUpdate
 * @package Library\Form\Category
 */
class CreateUpdate extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct($name = null)
    {
        parent::__construct('create_category');

        $this->add([
            'name'    => 'category_name',
            'type'    => 'Text',
            'options' => [
                'label' => 'Category Name:'
            ],
            'attributes' => [
                'style' => 'width: 300px; font-size: 12px; height: 28px;'
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
            'name' => 'category_id',
            'type' => 'Hidden'
        ]);

        $this->add([
            'name'    => 'sort_order',
            'type'    => 'Text',
            'options' => [
                'label' => 'Sort Order:'
            ]
        ]);

        $this->add([
            'name'    => 'parent',
            'type'    => 'Select',
            'options' => [
                'label' => 'Parent Category'
            ]
        ]);

        $this->add([
            'name'    => 'points_to',
            'type'    => 'Select',
            'options' => [
                'label' => 'Points To Category (Alias Category)'
            ]
        ]);

        $this->add([
            'name'    => 'query_list',
            'type'    => 'Select',
            'options' => [
                'label' => 'Dynamic Populated List'
            ]
        ]);

        $this->add([
            'name'    => 'description',
            'type'    => 'textarea',
            'options' => [
                'label' => 'Description'
            ]
        ]);

        $this->add([
            'name'    => 'keywords',
            'type'    => 'textarea',
            'options' => [
                'label' => 'Keywords'
            ]
        ]);

        $this->add([
            'name'    => 'meta_description',
            'type'    => 'textarea',
            'options' => [
                'label' => 'Meta Description'
            ]
        ]);

        $this->add([
            'name'    => 'meta_keywords',
            'type'    => 'textarea',
            'options' => [
                'label' => 'Meta Keywords'
            ]
        ]);

        $this->add([
            'name'       => 'image',
            'type'       => 'File',
            'options'    => [
                'label' => 'Category Photo'
            ],
            'attributes' => [
                'multiple' => false
            ]
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => 'Save Category'
            ]
        ]);
    }

    /**
     * @return InputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter)
        {

            $inputFilter = new InputFilter();
            $fileInput = new FileInput('image');
            $fileInput->setRequired(false);

            // Add validators
            $fileInput->getValidatorChain()
                ->attachByName('filesize', ['max' => 2097152])
                ->attachByName('fileimagesize', ['minWidth' => 100, 'minHeight' => 100])
                ->attachByName('Zend\Validator\File\Extension', ['extension' => ['gif', 'jpg', 'jpeg', 'png', 'bmp', 'dib']]);

            // Add filters
            $fileInput->getFilterChain()->attachByName('filerenameupload', [
                'target'               => getcwd() . '/public/img/category_images/',
                'randomize'            => true,
                'use_upload_extension' => true
            ]);

            // Add file filter
            $inputFilter->add($fileInput);

            $inputFilter->add([
                'name'     => 'category_name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'category_id',
                'required' => false,
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
                'name'     => 'parent',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'points_to',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'query_list',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'description',
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
                'name'     => 'meta_description',
                'required' => false,
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


            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }
}