<?php
/**
 * The ImageService class definition.
 *
 * This service handles various saving, editing and deleting functions on images
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;

use Library\Model\Media\Image;
use Library\Service\DB\EntityManagerSingleton;
use Zend\InputFilter\InputFilter;

/**
 * Class ImageService
 * @package Library\Service
 */
class ImageService extends AbstractService
{
    /**
     * Returns the file name of the passed in image temp name
     * @param string $temp_name
     *
     * @return string
     */
    public function getFileNameFromTempName($temp_name)
    {
        $pieces = explode('/', $temp_name);
        $file_name = end($pieces);

        return $file_name;
    }

    /**
     * Uploads and saves images to the database, returning an array of the images
     *
     * @param string[] $files
     * @param string $upload_folder
     * @param InputFilter $input_filter
     *
     * @return Image[]
     * @throws \Exception
     */
    public function save($files, $upload_folder = 'product_images', $input_filter = null)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $filter_service = $this->getServiceManager()->get('inputFilter');
        $images = [];

        if (count($files) > 0)
        {
            foreach ($files as $file)
            {
                // Filter the photo files
                $filtered_file = $filter_service->image($file, $upload_folder, $input_filter);
                $file_name = $this->getFileNameFromTempName($filtered_file['tmp_name']);

                // Save the file
                $image = new Image();
                $image->setUrl($file_name);
                $image->setInactive(false);
                $em->persist($image);
                $images[] = $image;
            }
        }

        return $images;
    }
}