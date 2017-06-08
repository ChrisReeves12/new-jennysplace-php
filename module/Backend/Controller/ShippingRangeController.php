<?php
/**
* The ShippingRangeController class definition.
*
* The controller that houses methods updating and creating shipping ranges
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Shipping\CreateUpdateRange;
use Library\Model\Shop\ShippingRange;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class ShippingRangeController
 * @package Backend\Controller
 */
class ShippingRangeController extends JPController
{
    public function singleAction()
    {
        // Get entity manager
        $shipping_range_service = $this->getServiceLocator()->get('shippingRange');
        $em = EntityManagerSingleton::getInstance();

        // Find id and range
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (isset($id))
        {
            $shipping_range = $em->getRepository('Library\Model\Shop\ShippingRange')->findOneById($id);
        }
        else
        {
            $shipping_range = null;
        }

        // Create form
        $create_update = new CreateUpdateRange();

        // Handle post
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $create_update->setData($data);

            if ($create_update->isValid())
            {
                $new_data = $create_update->getData();
                $shipping_range = $shipping_range_service->save($new_data, $shipping_range);
                $em->flush();
                return $this->redirect()->toUrl('/admin/shipping-range/single?id=' . $shipping_range->getId());
            }
        }

        // Fill in form
        if ($shipping_range instanceof ShippingRange)
        {
            $create_update->get('shipping_method')->setValue($shipping_range->getShippingMethod()->getId());
            $create_update->get('high_value')->setValue($shipping_range->getHighValue());
            $create_update->get('low_value')->setValue($shipping_range->getLowValue());
            $create_update->get('price')->setValue($shipping_range->getPrice());
        }

        $this->getServiceLocator()->get('ViewRenderer')->headLink()->appendStylesheet('/css/backend/shipping_range.css');

        return ['create_update_form' => $create_update];
    }
}