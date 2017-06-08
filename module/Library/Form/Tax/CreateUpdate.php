<?php
/**
* The CreateUpdate class definition.
*
* This form is used to modify a tax
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Tax;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateUpdate
 * @package Library\Form\Tax
 */
class CreateUpdate extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('create_update_tax');

        // Get US states
        $contents = file_get_contents(getcwd() . '/data/json/States.json');
        $json = json_decode($contents);

        foreach ($json as $state)
        {
            $states_options[$state->abbreviation] = $state->name;
        }

        $this->add([
            'name' => 'rate',
            'type' => 'text',
            'options' => [
                'label' => 'Rate'
            ]
        ]);

        $this->add([
            'name' => 'inactive',
            'type' => 'Checkbox',
            'options' => [
                'label' => 'Is Inactive?'
            ]
        ]);

        $this->add([
            'name' => 'state',
            'type' => 'select',
            'options' => [
                'label' => 'State'
            ],
            'attributes' => [
                'options' => $states_options
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
                'name'     => 'rate',
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
                'name'     => 'state',
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