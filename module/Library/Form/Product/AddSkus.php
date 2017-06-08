<?php
/**
* The AddSkus class definition.
*
* This form is used to add skus to products
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Product;
use Library\Form\MSForm;

/**
 * Class AddSkus
 * @package Library\Form\Product
 */
class AddSkus extends MSForm
{
    public function __construct()
    {
        parent::__construct('add_skus');

        $this->add([
            'name' => 'options',
            'type' => 'select',
            'options' => [
                'label' => 'Options'
            ]
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Save Skus'
            ]
        ]);
    }
}