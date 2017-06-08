<?php
/**
* The CustomPageController class definition.
*
* This controller houses all of the functions to modify custom pages
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\CustomPage\CreateUpdate;
use Library\Model\Page\CustomPage;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class CustomPageController
 * @package Backend\Controller
 */
class CustomPageController extends JPController
{
    public function singleAction()
    {
        $em = EntityManagerSingleton::getInstance();

        // Get page id
        $id = !empty($_GET['id']) ? $_GET['id'] : null;
        $custom_page = null;

        // Create form
        $create_update = new CreateUpdate();

        // Get custom page
        if (!is_null($id))
        {
            $custom_page = $em->getRepository('Library\Model\Page\CustomPage')->findOneById($id);
            if (!($custom_page instanceof CustomPage))
            {
                throw new \Exception("The Custom Page cannot be found in the datbase.");
            }

            $create_update->get('title')->setValue($custom_page->getPage()->getTitle());
            $create_update->get('content')->setValue($custom_page->getContent());
            $create_update->get('url_handle')->setValue($custom_page->getPage()->getUrlHandle());
            $create_update->get('inactive')->setValue($custom_page->getPage()->getInactive());
            $create_update->get('meta_keywords')->setValue($custom_page->getPage()->getKeywords());
            $create_update->get('meta_description')->setValue($custom_page->getPage()->getDescription());
        }

        // Handle post
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $create_update->setData($data);

            if ($create_update->isValid())
            {
                $new_data = $create_update->getData();
                $custom_page_service = $this->getServiceLocator()->get('customPage');
                $custom_page = $custom_page_service->save($new_data, $custom_page);
                $em->flush();

                $this->redirect()->toUrl('?id=' . $custom_page->getId());
            }
        }

        $this->getServiceLocator()->get('ViewRenderer')->headLink()->appendStylesheet('/css/backend/custom_page.css');

        return ['create_update' => $create_update];
    }
}