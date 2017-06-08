<?php
/**
* The CreateUpdate class definition.
*
* The menu used to create and update menus
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Menu;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateUpdate
 * @package Library\Form\Menu
 */
class CreateUpdate extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('create_edit_menu');

        $this->add([
            'name' => 'label',
            'type' => 'Text',
            'options' => [
                'label' => 'Label'
            ]
        ]);

        $this->add([
            'name' => 'css_class_name',
            'type' => 'Text',
            'options' => [
                'label' => 'CSS Class Name (Optional)'
            ]
        ]);

        $this->add([
            'name' => 'inactive',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Inactive?'
            ]
        ]);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        // not used
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter)
        {
            $inputFilter = new InputFilter();

            $inputFilter->add([
                'name' => 'label',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'css_class_name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'inactive',
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
}