<?php
/**
* The CreateUpdate class definition.
*
* This form is used for created and editing banners
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Banner;

use Library\Form\MSForm;
use Library\Service\DB\EntityManagerSingleton;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateUpdate
 * @package Library\Form\Banner
 */
class CreateUpdate extends MSForm
{

    protected $inputFilter;

    public function __construct($name = null)
    {
        parent::__construct('create_banner');

        $this->add([
            'name'    => 'label',
            'type'    => 'Text',
            'options' => [
                'label' => 'Label:'
            ]
        ]);

        $this->add([
            'name'       => 'anim_type',
            'type'       => 'Select',
            'options'    => [
                'label' => 'Transition Type:'
            ],
            'attributes' => [
                'options' => [
                    'fade'   => 'Fade',
                    'slide' => 'Slide'
                ]
            ]
        ]);

        $this->add([
            'name'    => 'anim_speed',
            'type'    => 'Text',
            'options' => [
                'label' => 'Transition Speed (milliseconds):'
            ]
        ]);

        $this->add([
            'name'    => 'delay_time',
            'type'    => 'Text',
            'options' => [
                'label' => 'Slide Delay Time (Seconds):'
            ]
        ]);

        $this->add([
            'name' => 'show_nav',
            'type' => 'Select',
            'options' => [
                'label' => 'Slide Navigation:'
            ],
            'attributes' => [
                'options' => [
                    1 => "On",
                    0 => "Off"
                ]
            ]
        ]);

        $this->add([
            'name' => 'show_arrows',
            'type' => 'Select',
            'options' => [
                'label' => 'Slide Direction Arrows:'
            ],
            'attributes' => [
                'options' => [
                    1 => "On",
                    0 => "Off"
                ]
            ]
        ]);

        $this->add([
            'name' => 'slide_direction',
            'type' => 'Select',
            'options' => [
                'label' => 'Slide Direction:'
            ],
            'attributes' => [
                'options' => [
                    'horizontal' => "Horizontal",
                    'vertical' => "Vertical"
                ]
            ]
        ]);

        $this->add([
            'name' => 'image_info',
            'type' => 'hidden',
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Save Banner'
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
                'name'     => 'label',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'show_nav',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'show_arrows',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'slide_direction',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'image_info',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'anim_speed',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'delay_time',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'anim_type',
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