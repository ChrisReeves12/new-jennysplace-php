<?php
/**
* The AddPhotos class definition.
*
* The part of the banners form used to add photos and banner slides
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Banner;

use Library\Form\MSForm;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;

/**
 * Class AddPhotos
 * @package Library\Form\Banner
 */
class AddPhotos extends MSForm
{
    protected $inputFilter;

    public function __construct($name = null)
    {

        parent::__construct('add_banner_photos');

        $this->add([
            'name'       => 'image',
            'type'       => 'File',
            'options'    => [
                'label' => 'Find Photo:'
            ],
            'attributes' => [
                'multiple' => true
            ]
        ]);
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
                ->attachByName('filesize', ['max' => 5097152])
                ->attachByName('fileimagesize', ['minWidth' => 32, 'minHeight' => 32])
                ->attachByName('Zend\Validator\File\Extension', ['extension' => ['gif', 'jpg', 'jpeg', 'png', 'bmp', 'dib']]);

            // Add filters
            $fileInput->getFilterChain()->attachByName('filerenameupload', [
                'target'               => getcwd() . '/public/img/banner_images/',
                'randomize'            => true,
                'use_upload_extension' => true
            ]);

            // Add file filter
            $inputFilter->add($fileInput);

            $inputFilter->add([
                'name'     => 'image_info',
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