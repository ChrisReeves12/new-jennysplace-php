<?php
/**
 * The InputFilterService class definition.
 *
 * This service is used for calling up various input filters
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;

use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class InputFilterService
 * @package Library\Service
 */
class InputFilterService extends AbstractService
{
    /**
     * Takes an array of information about a file and applies an input filter to it
     * for saving into the database
     *
     * @param $image_data
     * @param string $upload_folder
     * @param InputFilterInterface $input_filter
     *
     * @return array
     * @throws \Exception
     */
    public function image($image_data, $upload_folder = 'product_images', $input_filter = null)
    {
        // Create the filters if none is given
        if (!($input_filter instanceof InputFilterInterface))
        {
            $input_filter = new InputFilter();
            $file_filter = new FileInput('image');

            // Add validators
            $file_filter->getValidatorChain()->attachByName('filesize', ['max' => 2097152])->attachByName('fileimagesize', ['minWidth' => 100, 'minHeight' => 100])->attachByName('Zend\Validator\File\Extension', ['extension' => ['gif', 'jpg', 'jpeg', 'png', 'bmp', 'dib']]);

            // Add filters
            $file_filter->getFilterChain()->attachByName('filerenameupload', ['target' => getcwd() . '/public/img/' . $upload_folder . '/', 'randomize' => true, 'use_upload_extension' => true]);

            // Add file filter
            $input_filter->add($file_filter);
        }

        $input_filter->setData(['image' => $image_data]);
        if ($input_filter->isValid())
        {
            $filtered_data = $input_filter->getValues();
        }
        else
        {
            throw new \Exception("An error occured while moving uploaded image to correct server location.");
        }


        return $filtered_data['image'];
    }
}