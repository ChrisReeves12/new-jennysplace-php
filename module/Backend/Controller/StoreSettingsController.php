<?php
/**
* The StoreSettingsController class definition.
*
* This controller governs the backend screens where general store config is administered
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Settings\General;
use Library\Service\Settings;
use Zend\Config\Reader\Xml as XmlReader;

/**
 * Class StoreSettingsController
 * @package Backend\Controller
 */
class StoreSettingsController extends JPController
{
    protected $general_form;

    public function indexAction()
    {
        $reader = new XmlReader();

        // Create form to pass to view
        $this->general_form = new General();

        // Handle post
        $this->handle_post();

        // Load settings from config file
        $settings = $reader->fromFile(getcwd() . '/config/settings.xml');

        return ['general_form' => $this->general_form, 'settings' => $settings];
    }

    public function handle_post()
    {
        if ($this->getRequest()->isPost())
        {
            $files = $_FILES;
            $data = $this->getRequest()->getPost()->toArray();
            $data = array_merge_recursive($data, $files);
            $this->general_form->setData($data);

            if ($this->general_form->isValid())
            {
                $data = $this->general_form->getData();
                Settings::save($data);
            }
            else
            {
                throw new \Exception("Error saving settings. Make sure that all required fields are set.");
            }
        }
    }
}