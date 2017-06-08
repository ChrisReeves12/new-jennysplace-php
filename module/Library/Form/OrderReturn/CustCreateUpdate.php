<?php
/**
* The CustCreateUpdate class definition.
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
 * Class CustCreateUpdate
 * @package Library\Form\OrderReturn
 */
class CustCreateUpdate extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct($name = null)
    {
        parent::__construct('create_product');

        $this->add([
            'name' => 'message',
            'type' => 'textarea',
            'attributes' => [
                'placeholder' => 'Please type a message explaining why you want to return this item.'
            ]
        ]);

        $this->add([
            'name' => 'admin_message',
            'type' => 'hidden'
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
                'name'     => 'message',
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

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}