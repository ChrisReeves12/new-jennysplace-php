<?php
/**
* The CustomPageController class definition.
*
* Handles displaying of custom pages
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\Controller;

use Library\Controller\JPController;
use Library\Model\Page\Page;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class CustomPageController
 * @package Frontend\Controller
 */
class CustomPageController extends JPController
{
    public function indexAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Find page from handle
        $handle = $this->params()->fromRoute('handle');
        $page = $em->getRepository('Library\Model\Page\Page')->findOneBy(['url_handle' => $handle, 'page_type' => 'custom', 'inactive' => false]);
        if (!($page instanceof Page))
        {
            return $this->redirect()->toUrl('/');
        }

        // Check if this is invalid
        if ($page->getInactive() == true)
        {
            // Check if user is logged in as administrator
            $user = $user_service->getIdentity();
            if (!($user instanceof User) || ($user->getRole() != 'administrator' && $user->getRole() != 'superuser'))
            {
                return $this->redirect()->toUrl('/');
            }
        }

        // Set up page head tags
        $custom_page = $em->getRepository('Library\Model\Page\CustomPage')->findOneByPage($page);
        $this->getServiceLocator()->get('ViewRenderer')->headMeta()->appendProperty('description', $page->getDescription());
        $this->getServiceLocator()->get('ViewRenderer')->headMeta()->appendProperty('keywords', $page->getKeywords());
        $this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set($page->getTitle() . " - ");

        return ['custom_page' => $custom_page, 'page' => $page];
    }
}