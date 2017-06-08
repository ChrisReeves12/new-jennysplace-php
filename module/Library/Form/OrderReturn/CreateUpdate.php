<?php
/**
* The create_update class definition.
*
* The form that will update and create an order return request
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\OrderReturn;
use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateUpdate
 * @package Library\Form\OrderReturn
 */
class CreateUpdate extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct($name = null)
    {
        parent::__construct('create_product');

        $this->add([
            'name' => 'customer_message',
            'type' => 'textarea',
            'options' => [
                'label' => 'Customer Message'
            ]
        ]);

        $this->add([
            'name' => 'admin_message',
            'type' => 'textarea',
            'options' => [
                'label' => 'Admin Message'
            ]
        ]);

        $this->add([
            'name' => 'status',
            'type' => 'select',
            'options' => [
                'label' => 'Status'
            ],
            'attributes' => [
                'options' => [
                    'pending' => 'Pending',
                    'in_review' => 'In Reivew',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'canceled' => 'Canceled',
                ]
            ]
        ]);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter)
        {
            $inputFilter = new InputFilter();

            $inputFilter->add([
                'name'     => 'customer_message',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'admin_message',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'status',
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