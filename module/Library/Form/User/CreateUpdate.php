<?php
/**
* The CreateUpdate class definition.
*
* This form handles the creation and updating of users
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\User;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;

class CreateUpdate extends MSForm
{
    public function __construct()
    {
        parent::__construct('create_user');

        $this->add([
            'name' => 'registration_csrf',
            'type' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ]
        ]);

        $this->add([
            'name' => 'basic_info',
            'type' => 'Library\Form\User\BasicInfoFieldset'
        ]);

        $this->add([
            'name' => 'billing_address_info',
            'type' => 'Library\Form\User\AddressInfoFieldset'
        ]);

        $this->add([
            'name' => 'shipping_address_info',
            'type' => 'Library\Form\User\AddressInfoFieldset'
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Save User'
            ]
        ]);

        // Setup input filter
        $this->setInputFilter(new InputFilter());
    }
}