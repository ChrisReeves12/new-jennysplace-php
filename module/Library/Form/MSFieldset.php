<?php
/**
* The MSFieldset class definition.
*
* This class extends the Zend Framework 2 fieldset class for extra functionality
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Class MSFieldset
 * @package Library\Form
 */
class MSFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $inputFilterSpecification = [];

    /**
     * Sets the input filter specification array
     * @param array $spec
     */
    public function setInputFilterSpecification($spec)
    {
        $this->inputFilterSpecification = $spec;
    }

    /**
     * Returns the input filter specification array
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return $this->inputFilterSpecification;
    }
}