<?php
/**
* The CreateUpdate class definition.
*
* This class updates and creates custom pages
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\CustomPage;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateUpdate
 * @package Library\Form\CustomPage
 */
class CreateUpdate extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('create_update_custom_page');

        $this->add([
            'name' => 'title',
            'type' => 'text',
            'options' => [
                'label' => 'Title'
            ]
        ]);

        $this->add([
            'name' => 'url_handle',
            'type' => 'text',
            'options' => [
                'label' => 'URL Handle'
            ]
        ]);

        $this->add([
            'name' => 'meta_description',
            'type' => 'textarea',
            'options' => [
                'label' => 'Meta Description'
            ]
        ]);

        $this->add([
            'name' => 'meta_keywords',
            'type' => 'textarea',
            'options' => [
                'label' => 'Meta Keywords'
            ]
        ]);

        $this->add([
            'name' => 'inactive',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Inactive'
            ]
        ]);

        $this->add([
            'name' => 'content',
            'type' => 'textarea',
            'options' => [
                'label' => 'Content'
            ],
            'attributes' => [
                'id' => 'editor'
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
                'name'     => 'title',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'url_handle',
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
                'name'     => 'meta_description',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'content',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'meta_keywords',
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