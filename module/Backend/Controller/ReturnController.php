<?php
/**
* The ReturnController class definition.
*
* The main controller used to administer returns
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\OrderReturn\CreateUpdate;
use Library\Model\Shop\ProductReturn;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class ReturnController
 * @package Backend\Controller
 */
class ReturnController extends JPController
{
    /**
     * The page to alter a single return
     * @return array
     * @throws \Exception
     */
    public function singleAction()
    {
        $order_service = $this->getServiceLocator()->get('order');
        $create_update = new CreateUpdate();

        $this->getServiceLocator()->get('ViewRenderer')->headLink()->appendStylesheet('/css/backend/return.css');

        // Find incoming return
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $em = EntityManagerSingleton::getInstance();
        $return = null;

        if (!is_null($id))
        {
            $return = $em->getRepository('Library\Model\Shop\ProductReturn')->findOneById($id);

            if (!($return instanceof ProductReturn))
            {
                throw new \Exception("The product return cannot be found in the database.");
            }

            $create_update->get('admin_message')->setValue($return->getAdminMessage());
            $create_update->get('customer_message')->setValue($return->getCustomerMessage());
            $create_update->get('status')->setValue($return->getStatus());
        }

        // Handle posts
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $create_update->setData($data);
            if ($create_update->isValid())
            {
                // Save return
                $new_data = $create_update->getData();
                $order_service->saveOrderReturn($new_data, $return);
                $em->flush();
            }
        }

        return ['create_update' => $create_update, 'return' => $return];
    }
}