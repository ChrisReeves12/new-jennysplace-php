<?php
/**
* The DiscountController class definition.
*
* This class helps us to view and edit discounts.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Discount\Action;
use Library\Form\Discount\CreateUpdate;
use Library\Model\Relationship\DiscountDiscountAction;
use Library\Model\Shop\Discount;
use Library\Model\Shop\DiscountAction;
use Library\Service\DB\EntityManagerSingleton;
use Zend\View\Model\JsonModel;

/**
 * Class DiscountController
 * @package Backend\Controller
 */
class DiscountController extends JPController
{
    protected $discount_form;
    protected $discount_action_form;
    protected $discount_id;
    protected $discount;

    /**
     * Shows a single discount and its values
     *
     * @return array
     * @throws \Exception
     */
    public function singleAction()
    {
        $em = EntityManagerSingleton::getInstance();
        $this->discount_id = isset($_GET['id']) ? $_GET['id'] : null;

        // Create form for discount editing
        $this->discount_form = new CreateUpdate();
        $this->discount_action_form = new Action();
        $discount_action_content = "";

        // Fill up form if id is passed in
        if (!empty($this->discount_id))
        {
            $this->discount = $em->getRepository('Library\Model\Shop\Discount')->findOneById($this->discount_id);
            if (!($this->discount instanceof Discount))
            {
                throw new \Exception("The ID passed in does not match a discount that is currently in the database.");
            }

            $this->discount_form->get('discount')->setValue($this->discount_id);
            $this->discount_form->get('discount_name')->setValue($this->discount->getName());
            $this->discount_form->get('discount_script')->setValue($this->discount->getScriptName());
            $this->discount_form->get('discount_code')->setValue($this->discount->getCode());
            $this->discount_form->get('dollar_hurdle')->setValue($this->discount->getDollarHurdle());
            $this->discount_form->get('start_date')->setValue($this->discount->getStartDate()->format("m/d/Y"));
            $this->discount_form->get('end_date')->setValue($this->discount->getEndDate()->format("m/d/Y"));
            $this->discount_form->get('inactive')->setValue($this->discount->getIsInactive());

            // Get attributes contents
            $discount_action_rels = $this->discount->getDiscountDiscountActions();
            if (count($discount_action_rels) > 0)
            {
                foreach ($discount_action_rels as $discount_action_rel)
                {
                    $discount_action = $discount_action_rel->getDiscountAction();
                    if (!($discount_action instanceof DiscountAction))
                        continue;

                    $discount_action_content .= "<div data-id='".$discount_action->getId()."' class='discount_action'><a class='delete' href=''>[Close]</a> ".$discount_action->getName()."</div>";
                }

            }

        }

        // Handle incoming posts
        $response = $this->_handle_post();
        if (!empty($response))
        {
            return new JsonModel($response);
        }

        // Get list of discounts to populate select form
        $discounts = $em->getRepository('Library\Model\Shop\Discount')->findAll();
        $discount_options = [0 => 'Select Discount'];

        if (count($discounts) > 0)
        {
            foreach ($discounts as $discount)
            {
                $discount_options[$discount->getId()] = $discount->getName();
            }
        }

        $this->discount_form->get('discount')->setAttribute('options', $discount_options);

        // Get list of discount actions to populate form
        $discount_actions = $em->getRepository('Library\Model\Shop\DiscountAction')->findAll();
        $discount_action_options = [0 => 'Select Discount Action'];

        if (count($discount_actions) > 0)
        {
            foreach ($discount_actions as $discount_action)
            {
                $discount_action_options[$discount_action->getId()] = $discount_action->getName();
            }
        }

        $this->discount_action_form->get('discountaction')->setAttribute('options', $discount_action_options);
        $this->discount_form->get('discount_action')->setAttribute('options', $discount_action_options);

        // Get shipping methods for discount action form
        $shipping_methods = $em->getRepository('Library\Model\Shop\ShippingMethod')->findBy(['inactive' => false]);
        $shipping_method_options = [0 => 'Select Shipping Method'];

        if (count($shipping_methods) > 0)
        {
            foreach ($shipping_methods as $shipping_method)
            {
                $shipping_method_options[$shipping_method->getId()] = $shipping_method->getName();
            }
        }

        $this->discount_action_form->get('shipping_method')->setAttribute('options', $shipping_method_options);

        // Attach javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/discount.js');

        return ['discount_form' => $this->discount_form, 'discount_action_content' => $discount_action_content, 'discount_action_form' => $this->discount_action_form];
    }

    /**
     * Handles various post events
     * @return array
     * @throws \Exception
     */
    private function _handle_post()
    {
        $discount_service = $this->getServiceLocator()->get('discount');

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $task = $data['task'];
            unset($data['task']);

            switch ($task)
            {
                case 'save_discount':

                    $this->discount_form->setData($data);

                    if ($this->discount_form->isValid())
                    {
                        $data = $this->discount_form->getData();
                        $discount = $discount_service->save_discount($data);
                        EntityManagerSingleton::getInstance()->flush();
                        return ['error' => false, 'discount' => $discount->toArray()];
                    }
                    else
                    {
                        throw new \Exception("Please make sure all required forms are filled in.");
                    }
                    break;

                case 'delete_discount':

                    $discount_service->deleteByIds([$data['discount_id']], new Discount());
                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false];
                    break;

                case 'save_discount_action':

                    // Verify the form
                    $this->discount_action_form->setData($data);
                    if ($this->discount_action_form->isValid())
                    {
                        $new_data = $this->discount_action_form->getData();
                        $discount_service->save_action($new_data);
                        EntityManagerSingleton::getInstance()->flush();
                    }
                    break;

                case 'show_discount':

                    $discount = EntityManagerSingleton::getInstance()->getRepository('Library\Model\Shop\Discount')->findOneById($data['discount_id']);

                    if ($discount instanceof Discount)
                    {
                        $discount_info = $discount->toArray();

                        // Get discount actions
                        $discount_action_rels = $discount->getDiscountDiscountActions();
                        if (count($discount_action_rels) > 0)
                        {
                            foreach ($discount_action_rels as $discount_action_rel)
                            {
                                if (!($discount_action_rel instanceof DiscountDiscountAction))
                                    continue;

                                $discount_action = $discount_action_rel->getDiscountAction();
                                $discount_info['discount_actions'][] = $discount_action->toArray();
                            }
                        }

                        return ['error' => false, 'discount_info' => $discount_info];
                    }
                    break;

                case 'show_discount_action':

                    $discount_action_id = $data['discountaction'];
                    $discount_action = EntityManagerSingleton::getInstance()->getRepository('Library\Model\Shop\DiscountAction')->findOneById($discount_action_id);
                    $discount_action_info = $discount_action->toArray();

                    return ['error' => false, 'discount_action_info' => $discount_action_info];
                    break;

                case 'delete_discount_action':

                    $discount_service->deleteByIds($data['discountaction'], new DiscountAction());
                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false];
                    break;
            }
        }

        return null;
    }
}