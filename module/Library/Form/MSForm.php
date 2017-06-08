<?php
/**
* The MSForm class definition.
*
* This class extends the normal Zend Framework 2 form for additional functionality
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form;

use Zend\Form\Form;

/**
 * Class MSForm
 * @package Library\Form
 */
class MSForm extends Form
{
    /**
     * Sets up filter and validation specifications for each fieldset of the form
     * @param string $fieldset
     * @param array $specification
     */
    public function setupFilterSpecs($fieldset, $specification)
    {
        $this->get($fieldset)->setInputFilterSpecification($specification);
    }
}