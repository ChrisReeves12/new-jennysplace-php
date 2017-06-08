<?php
/**
 * The EmailCreateUpdate class definition.
 *
 * This form is for creating and modifying emails for campaigns
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Form\Mailer;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class EmailCreateUpdate
 * @package Library\Form\Mailer
 */
class EmailCreateUpdate extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('create_campaign');

        $this->add([
            'name' => 'subject',
            'type' => 'Text',
            'options' => [
                'label' => 'Subject'
            ]
        ]);

        $this->add([
            'name' => 'from',
            'type' => 'Text',
            'options' => [
                'label' => 'From'
            ]
        ]);

        $this->add([
            'name' => 'scheduled_send_time',
            'type' => 'Text',
            'options' => [
                'label' => 'Scheduled Send Time'
            ]
        ]);

        $this->add([
            'name' => 'campaign',
            'type' => 'Select',
            'options' => [
                'label' => 'Campaign'
            ]
        ]);

        $this->add([
            'name' => 'message',
            'type' => 'textarea',
            'options' => [
                'label' => 'Message'
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
                'name' => 'subject',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'from',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'scheduled_send_time',
                'required' => false,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'campaign',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'message',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ]
            ]);
        }
    }
}