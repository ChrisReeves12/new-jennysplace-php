<?php
/**
* The MailistSignup class definition.
*
* The mail list signup form
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Subscription;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class MailistSignup
 * @package Library\Form\Subscription
 */
class MailistSignup extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('mail_list_signup');

        $this->add([
            'name'    => 'name',
            'type'    => 'Text',
            'attributes' => [
                'placeholder' => 'Name'
            ]
        ]);

        $this->add([
            'name'    => 'email',
            'type'    => 'Text',
            'attributes' => [
                'placeholder' => 'Email'
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
                'name'     => 'name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'email',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'EmailAddress']
                ]
            ]);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}