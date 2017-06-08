<?php
/**
* The ShippingMethodController class definition.
*
* The shipping method controller that allows for modification and creation of shipping
* methods.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Shipping\CreateUpdateMethod;
use Library\Model\Shop\ShippingMethod;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class ShippingMethodController
 * @package Backend\Controller
 */
class ShippingMethodController extends JPController
{
    public function singleAction()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $shipping_method_service = $this->getServiceLocator()->get('shippingMethod');

        // Find id and range
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (isset($id))
        {
            $shipping_method = $em->getRepository('Library\Model\Shop\ShippingMethod')->findOneById($id);
        }
        else
        {
            $shipping_method = null;
        }

        // Load form and hydrate
        $create_update = new CreateUpdateMethod();
        if ($shipping_method instanceof ShippingMethod)
        {
            $create_update->get('name')->setValue($shipping_method->getName());
            $create_update->get('carrier')->setValue($shipping_method->getCarrier());
            $create_update->get('carrier_id')->setValue($shipping_method->getCarrierId());
            $create_update->get('inactive')->setValue($shipping_method->isInactive());
        }

        // Handle posts
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $create_update->setData($data);
            if ($create_update->isValid())
            {
                $new_data = $create_update->getData();
                $shipping_method = $shipping_method_service->save($new_data, $shipping_method);
                $em->flush();

                // Send to page
                if (!isset($id))
                {
                    $this->redirect()->toUrl('/admin/shipping-method/single?id=' . $shipping_method->getId());
                }
            }
        }

        return ['create_update' => $create_update];
    }
}