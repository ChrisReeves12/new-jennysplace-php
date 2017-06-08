<?php
/**
* The CreateUpdate class definition.
*
* The form that creates and updates the form
 *
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Option;
use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;

/**
 * Class CreateUpdate
 * @package Library\Form\Option
 */
class CreateUpdate extends MSForm
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('create_option');

        $this->add([
            'name'    => 'name',
            'type'    => 'Text',
            'options' => [
                'label' => 'Name'
            ]
        ]);

        $this->add([
            'name'    => 'value_data',
            'type'    => 'Hidden'
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => [
                'value' => 'Save Option'
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
                'name'     => 'value_data',
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