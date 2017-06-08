<?php
/**
* The AddImages class definition.
*
* This adds additional images to the product
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Product;

use Library\Form\MSForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class AddImages
 * @package Library\Form\Product
 */
class AddImages extends MSForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct($name = null)
    {
        parent::__construct('add_product_photos');

        $this->add([
            'name'       => 'image',
            'type'       => 'File',
            'options'    => [
                'label' => 'Find Photo'
            ],
            'attributes' => [
                'multiple' => true
            ]
        ]);

        $this->add([
            'name'       => 'add_image',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Add Images'
            ]
        ]);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    public function getInputFilter()
    {

        if (!$this->inputFilter)
        {

            $inputFilter = new InputFilter();
            $fileInput = new FileInput('image');
            $fileInput->setRequired(true);

            // Add validators
            $fileInput->getValidatorChain()
                ->attachByName('filesize', ['max' => 2097152])
                ->attachByName('fileimagesize', ['minWidth' => 100, 'minHeight' => 100])
                ->attachByName('Zend\Validator\File\Extension', ['extension' => ['gif', 'jpg', 'jpeg', 'png', 'bmp', 'dib']]);

            // Add filters
            $fileInput->getFilterChain()->attachByName('filerenameupload', [
                'target'               => getcwd() . '/public/img/product_images/',
                'randomize'            => true,
                'use_upload_extension' => true
            ]);

            // Add file filter
            $inputFilter->add($fileInput);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}