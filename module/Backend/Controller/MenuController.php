<?php
/**
* The MenuController class definition.
*
* This controller manages all of the actions regarding menus
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Menu\CreateUpdate;
use Library\Model\Page\Menu;
use Library\Service\DB\EntityManagerSingleton;
use Zend\View\Model\JsonModel;

/**
 * Class MenuController
 * @package Backend\Controller
 */
class MenuController extends JPController
{
    public function singleAction()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $page_service = $this->getServiceLocator()->get('page');
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $create_update_form = new CreateUpdate();

        // Try to find the menu being edited
        $menu = $em->getRepository('Library\Model\Page\Menu')->findOneById($id);
        if (!is_null($id))
        {
            if (!($menu instanceof Menu))
            {
                throw new \Exception("The menu being edited cannot be found in the database");
            }
            else
            {
                // Populate the form
                $create_update_form->get('label')->setValue($menu->getLabel());
                $create_update_form->get('css_class_name')->setValue($menu->getCssClass());
                $create_update_form->get('inactive')->setValue($menu->getInactive());
            }
        }

        // Handle incoming post
        if ($this->getRequest()->isPost())
        {
            // Save or update
            $data = $this->getRequest()->getPost()->toArray();
            $saved_menu = $page_service->save_menu($data, $menu);
            $em->flush();

            return new JsonModel(['error' => false, 'menu_id' => $saved_menu->getId()]);
        }

        $menu_items = !is_null($menu) ? $menu->getMenuItems() : [];

        // Attach stylesheet
        $this->getServiceLocator()->get('ViewRenderer')->headLink()->appendStylesheet('/css/backend/menu.css');
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/menu.js');

        return ['menu' => $menu, 'menu_items' => $menu_items, 'create_update_form' => $create_update_form];
    }
}