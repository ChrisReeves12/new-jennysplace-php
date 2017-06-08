<?php
/**
* The General class definition.
*
* This service manages the creation and updating of general store settings
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Zend\Config\Reader\Xml as XmlReader;
use Zend\Config\Writer\Xml as XmlWriter;

/**
 * Class General
 * @package Library\Service
 */
class Settings
{
    /**
     * Saves data to the general store settings
     *
     * @param array $data
     * @throws \Exception
     */
    static public function save($data)
    {
        $reader = new XmlReader();
        $writer = new XmlWriter();

        // Check if store logo has been set
        if (empty($data['store_logo']['tmp_name']))
        {
            unset($data['store_logo']);
        }

        // Get config file
        $settings_file = getcwd() . '/config/settings.xml';

        $old_settings = $reader->fromFile($settings_file);
        if (empty($data['store_logo']))
        {
            $data['store_logo'] = $old_settings['store_logo'];
        }

        // Check if file is writable
        if (!is_writable($settings_file))
        {
            throw new \Exception("The settings file is not writable. Please make sure permissions are set correctly on the settings file on the server.");
        }

        $xml_output = $writer->processConfig($data);
        $result = file_put_contents($settings_file, $xml_output);

        if (!$result)
        {
            throw new \Exception("Error saving store settings, please try again later.");
        }
    }

    /**
     * Get a setting from the general settings
     * @param string $setting
     * @return array
     */
    static public function get($setting)
    {
        $reader = new XmlReader();
        $general_settings_info = $reader->fromFile(getcwd() . '/config/settings.xml');
        return $general_settings_info[$setting];
    }

    /**
     * Returns an array of all the setting values
     * @return array
     */
    static public function getAll()
    {
        $reader = new XmlReader();
        return $reader->fromFile(getcwd() . '/config/settings.xml');

    }
}