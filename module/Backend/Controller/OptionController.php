<?php
/**
* The OptionController class definition.
*
* This controller handles the administration of product options and values.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Option\CreateUpdate;
use Library\Service\DB\EntityManagerSingleton;
use Zend\View\Model\JsonModel;

/**
 * Class OptionController
 * @package Backend\Controller
 */
class OptionController extends JPController
{
    protected $option;
    protected $option_id;
    protected $options_form;

    /**
     * Shows a single option and its values
     *
     * @return array
     * @throws \Exception
     */
    public function singleAction()
    {
        // Create the form and collect option values
        $em = EntityManagerSingleton::getInstance();

        $this->options_form = new CreateUpdate();
        $option_values = $em->getRepository('Library\Model\Product\OptionValue')->findAll();

        $options = [];
        if (count($option_values) > 0)
        {
            foreach($option_values as $option_value)
            {
                $options[$option_value->getId()] = $option_value->getName();
            }
        }

        // Check if an id is being passed in
        $this->option_id = (!empty($_GET['id'])) ? $_GET['id'] : null;
        if (!empty($this->option_id))
        {
            $this->option = $em->getRepository('Library\Model\Product\Option')->findOneById($this->option_id);
        }
        else
        {
            $this->option = null;
        }

        // Handle posts
        $response = $this->_handle_post();
        if (!empty($response))
        {
            return new JsonModel($response);
        }

        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/option.js');

        return ['options_form' => $this->options_form, 'options' => $options, 'option' => $this->option];
    }

    /**
     * Handle incoming posts
     * @return array
     */
    private function _handle_post()
    {
        $option_service = $this->getServiceLocator()->get('option');

        if ($this->getRequest()->isPost())
        {
            $em = EntityManagerSingleton::getInstance();
            $data = $this->getRequest()->getPost()->toArray();
            $task = $data['task'];
            unset($data['task']);

            switch ($task)
            {
                case 'save_option':
                    $this->options_form->setData($data);
                    if ($this->options_form->isValid())
                    {
                        $data = $this->options_form->getData();
                        $this->option = $option_service->save($data, $this->option_id);
                        $em->flush();

                        // Refresh the page
                        return $this->redirect()->toUrl($_SERVER['REDIRECT_URL'] . '?id=' . $this->option->getId());
                    }

                    break;

                case 'add_option_value':

                    $option_value = $option_service->save_option_value($data, null);
                    $em->flush();

                    return ['error' => false, 'option_value_id' => $option_value->getId()];
                    break;

                case 'delete_option_value':

                    $option_service->delete_option_value($data['value_id']);
                    $em->flush();

                    return ['error' => false];
                    break;

                case 'update_option_value':

                    $option_service->update_option_value($data);
                    $em->flush();

                    return ['error' => false];
                    break;
            }
        }

        return null;
    }
}