<?php
/**
* The Login class definition.
*
* The form that logs in users
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Auth;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;

/**
 * Class Login
 * @package Library\Form\Auth
 */
class Login extends MSForm
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('login_form');

        $this->add([
            'name'    => 'email',
            'type'    => 'Text',
            'options' => [
                'label' => 'Email'
            ],
            'attributes' => [
                'placeholder' => 'Email'
            ]
        ]);

        $this->add([
            'name'    => 'password',
            'type'    => 'Password',
            'options' => [
                'label' => 'Password'
            ],
            'attributes' => [
                'placeholder' => 'Password'
            ]
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => [
                'value' => 'Log In'
            ]
        ]);

        $this->add([
            'name' => 'login_csrf',
            'type' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ]
        ]);
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter)
        {
            $inputFilter = new InputFilter();

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

            $inputFilter->add([
                'name'     => 'password',
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