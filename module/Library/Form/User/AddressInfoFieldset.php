<?php
/**
* The AddressInfoFieldset class definition.
*
* This fieldset takes the address information of the user
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\User;

use Library\Form\MSFieldset;

class AddressInfoFieldset extends MSFieldset
{
    public function __construct()
    {
        parent::__construct('address_info');

        // Get US states
        $contents = file_get_contents(getcwd() . '/data/json/States.json');
        $json = json_decode($contents);

        foreach ($json as $state)
        {
            $states_options[$state->abbreviation] = $state->name;
        }

        $this->add([
            'name' => 'first_name',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'First Name'
            ]
        ]);

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
            'name' => 'line_1',
            'type' => 'text',
            'attributes' => [
                    'class' => 'form-control',
                    'placeholder' => 'Address Line 1'
                ]
            ]
        );

        $this->add([
            'name' => 'line_2',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Address Line 2'
            ]
        ]);

        $this->add([
            'name' => 'company',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Company'
            ]
        ]);

        $this->add([
            'name' => 'phone',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Phone Number'
            ]
        ]);

        $this->add([
                'name' => 'city',
                'type' => 'text',
                'attributes' => [
                    'class' => 'form-control',
                    'placeholder' => 'City'
                ]
            ]
        );

        $this->add([
                'name' => 'state',
                'type' => 'Select',
                'attributes' => [
                    'options' => $states_options,
                    'class' => 'form-control'
                ]
            ]
        );

        $this->add([
                'name' => 'zipcode',
                'type' => 'text',
                'attributes' => [
                    'class' => 'form-control',
                    'placeholder' => 'Zip Code'
                ]
            ]
        );
    }
}