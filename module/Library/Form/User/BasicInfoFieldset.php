<?php
/**
* The BasicInfoFieldset class definition.
*
* This fieldset takes the basic information of the user.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\User;

use Library\Form\MSFieldset;

class BasicInfoFieldset extends MSFieldset
{
    public function __construct()
    {
        parent::__construct('basic_info');

        $this->add([
            'name' => 'first_name',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'First Name'
                ]
            ]
        );

        $this->add([
            'name' => 'last_name',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Last Name'
            ]
        ]);

        $this->add([
            'name' => 'email',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Email'
            ]
        ]);

        $this->add([
            'name' => 'password',
            'type' => 'password',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Password'
            ]
        ]);

        $this->add([
            'name' => 'cpassword',
            'type' => 'password',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Confirm Password'
            ]
        ]);

        $this->add([
            'name' => 'tax_id',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Business Tax ID (XX-XXXXXXX)',
                'maxlength' => 10
            ]
        ]);

        $this->add([
            'name' => 'newsletter',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Newsletter'
            ],
            'attributes' => [
                'checked' => 'checked'
            ]
        ]);
    }
}