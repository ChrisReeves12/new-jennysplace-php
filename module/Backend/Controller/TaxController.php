<?php
/**
* The TaxController class definition.
*
* This contains all of the functions used to update and create taxes
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Tax\CreateUpdate;
use Library\Model\Shop\Tax;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class TaxController
 * @package Backend\Controller
 */
class TaxController extends JPController
{
    public function singleAction()
    {
        $tax_service = $this->getServiceLocator()->get('tax');

        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $create_update = new CreateUpdate();

        // Get tax if being loaded
        $tax = null;
        $id = isset($_GET['id']) ? $_GET['id'] : null;

        if (!is_null($id))
        {
            $tax = $em->getRepository('Library\Model\Shop\Tax')->findOneById($id);
            if (!($tax instanceof Tax))
            {
                throw new \Exception("The id to load the tax object cannot be found in the database.");
            }

            // Hydrate the form
            $create_update->get('rate')->setValue($tax->getRate());
            $create_update->get('state')->setValue($tax->getState());
            $create_update->get('inactive')->setValue($tax->getInactive());
        }

        // Handle post
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $create_update->setData($data);

            if ($create_update->isValid())
            {
                $new_data = $create_update->getData();
                $tax = $tax_service->save($new_data, $tax);
                $em->flush();

                return $this->redirect()->toUrl('?id=' . $tax->getId());
            }
        }

        return ['create_update' => $create_update];
    }
}