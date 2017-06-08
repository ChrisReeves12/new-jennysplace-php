<?php
/**
* The UserController class definition.
*
* The user controller handles the saving and editing of users
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\User\CreateUpdate;
use Library\Form\User\InputFilter\FilterSpec;
use Library\Model\Shop\ShopList\Order;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;
use Zend\InputFilter\InputFilter;
use Zend\View\Model\JsonModel;

/**
 * Class UserController
 * @package Backend\Controller
 */
class UserController extends JPController
{
    /** @var CreateUpdate */
    protected $create_update_form;
    protected $user;
    protected $user_id;

    /**
     * Shows a single page of a user for editing and updating
     * @return array
     * @throws \Exception
     */
    public function singleAction()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Create form for modifying the user
        $this->create_update_form = new CreateUpdate();

        // Build forms
        $this->_build_form();

        // Load user if id is passed in
        $this->user_id = isset($_GET['id']) ? $_GET['id'] : null;

        if (!is_null($this->user_id))
        {
            $this->user = $em->getRepository('Library\Model\User\User')->findOneById($this->user_id);
            if (!($this->user instanceof User))
            {
                throw new \Exception("The user being edited cannot be found in the database.");
            }

            // Get saved shopping cart if applicable
            $saved_cart = $this->user->getSavedCart();

            // Hydrate the form
            $this->_hydrate_form();
        }
        else
        {
            $saved_cart = null;
        }

        // Handle post requests
        $response = $this->_handle_post();
        if (!empty($response))
        {
            return new JsonModel($response);
        }

        // Get user orders
        $user_orders = $em->getRepository('Library\Model\Shop\ShopList\Order')->findBy(['user' => $this->user]);

        // Attach javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/user.js');

        return [
            'create_update_form' => $this->create_update_form,
            'user_orders' => $user_orders,
            'saved_cart' => $saved_cart
        ];
    }

    /**
     * Build and setup form and validators
     */
    private function _build_form()
    {
        // Add role for backend
        $this->create_update_form->get('basic_info')->add([
            'name' => 'role',
            'type' => 'select',
            'options' => [
                'label' => 'Role'
            ],
            'attributes' => [
                'options' => [
                    'customer' => 'Customer',
                    'administrator' => 'Administrator'
                ]
            ]
        ]);

        // Add store credit field
        $this->create_update_form->get('basic_info')->add([
            'name' => 'store_credit',
            'type' => 'text',
            'options' => [
                'label' => 'Store Credit'
            ]
        ]);

        // Add memo field
        $this->create_update_form->get('basic_info')->add([
            'name' => 'admin_memo',
            'type' => 'textarea',
            'options' => [
                'label' => 'Administrator Memo'
            ]
        ]);

        // Add status for backend
        $this->create_update_form->get('basic_info')->add([
            'name' => 'status',
            'type' => 'select',
            'options' => [
                'label' => 'Status'
            ],
            'attributes' => [
                'options' => [
                    1 => 'Verified',
                    0 => 'Unverified'
                ]
            ]
        ]);

        // Remove name fields for billing address
        $this->create_update_form->get('billing_address_info')->remove('first_name');
        $this->create_update_form->get('billing_address_info')->remove('last_name');

        // Setup filter and validation specifications
        $this->create_update_form->setupFilterSpecs('basic_info', FilterSpec::getBackendBasicInfoSpec());
        $this->create_update_form->setupFilterSpecs('billing_address_info', FilterSpec::getBillingAddressSpec());
        $this->create_update_form->setupFilterSpecs('shipping_address_info', FilterSpec::getShippingAddressSpec());
    }

    /**
     * Handles various post requests
     * @throws \Exception
     * @return array
     */
    private function _handle_post()
    {
        $discount_service = $this->getServiceLocator()->get('discount');
        $order_service = $this->getServiceLocator()->get('order');
        $user_service = $this->getServiceLocator()->get('user');

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $task = $data['task'];
            unset($data['task']);

            // Perform action based on task
            switch ($task)
            {
                case 'create_update':

                    // Validate form
                    $this->create_update_form->setData($data);
                    $this->create_update_form->setupFilterSpecs('basic_info', FilterSpec::getBackendBasicInfoSpec());
                    if ($this->create_update_form->isValid())
                    {
                        $valid_data = $this->create_update_form->getData();

                        // Check if there is a user with this email
                        $em = EntityManagerSingleton::getInstance();
                        $current_user = $this->user;

                        $other_user = $em->getRepository('Library\Model\User\User')->findOneBy(['email' => $valid_data['basic_info']['email']]);
                        if ($other_user instanceof User)
                        {
                            if ($current_user instanceof User)
                            {
                                if ($other_user->getId() != $current_user->getId())
                                {
                                    $this->create_update_form->get('basic_info')->get('email')->setMessages(['A user with this email already exists, please use another email.']);
                                    break;
                                }
                            }
                            else
                            {
                                $this->create_update_form->get('basic_info')->get('email')->setMessages(['A user with this email already exists, please use another email.']);
                                break;
                            }
                        }

                        $this->user = $user_service->save($valid_data, $this->user);

                        EntityManagerSingleton::getInstance()->flush();

                        // Refresh the page
                        return $this->redirect()->toUrl($_SERVER['REDIRECT_URL'] . '?id=' . $this->user->getId());
                    }
                    break;

                case 'delete_user_orders':

                    // Delete orders from list
                    $order_service->deleteByIds($data['ids'], new Order());
                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false];
                    break;

                case 'remove_discount':

                    // Remove discount
                    $discount_service->remove_list_discount($data['discount_assoc_id']);
                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false];
                    break;
            }
        }

        return null;
    }

    /**
     * Fill up the form with customer information
     */
    private function _hydrate_form()
    {
        // Hydrate
        $this->create_update_form->get('basic_info')->get('first_name')->setValue($this->user->getFirstName());
        $this->create_update_form->get('basic_info')->get('last_name')->setValue($this->user->getLastName());
        $this->create_update_form->get('basic_info')->get('tax_id')->setValue($this->user->getTaxId());
        $this->create_update_form->get('basic_info')->get('email')->setValue($this->user->getEmail());
        $this->create_update_form->get('basic_info')->get('role')->setValue($this->user->getRole());
        $this->create_update_form->get('basic_info')->get('status')->setValue($this->user->getStatus());
        $this->create_update_form->get('basic_info')->get('newsletter')->setValue($this->user->getNewsletter());
        $this->create_update_form->get('basic_info')->get('store_credit')->setValue($this->user->getStoreCredit());
        $this->create_update_form->get('basic_info')->get('admin_memo')->setValue($this->user->getAdminMemo());

        if (!is_null($this->user->getBillingAddress()))
        {
            $this->create_update_form->get('billing_address_info')->get('line_1')->setValue($this->user->getBillingAddress()->getLine1());
            $this->create_update_form->get('billing_address_info')->get('line_2')->setValue($this->user->getBillingAddress()->getLine2());
            $this->create_update_form->get('billing_address_info')->get('city')->setValue($this->user->getBillingAddress()->getCity());
            $this->create_update_form->get('billing_address_info')->get('state')->setValue($this->user->getBillingAddress()->getState());
            $this->create_update_form->get('billing_address_info')->get('zipcode')->setValue($this->user->getBillingAddress()->getZipcode());
            $this->create_update_form->get('billing_address_info')->get('phone')->setValue($this->user->getBillingAddress()->getPhone());
            $this->create_update_form->get('billing_address_info')->get('company')->setValue($this->user->getBillingAddress()->getCompany());
            $this->create_update_form->get('billing_address_info')->get('email')->setValue($this->user->getBillingAddress()->getEmail());
        }

        if (!is_null($this->user->getShippingAddress()))
        {
            $this->create_update_form->get('shipping_address_info')->get('first_name')->setValue($this->user->getShippingAddress()->getFirstName());
            $this->create_update_form->get('shipping_address_info')->get('last_name')->setValue($this->user->getShippingAddress()->getLastName());
            $this->create_update_form->get('shipping_address_info')->get('line_1')->setValue($this->user->getShippingAddress()->getLine1());
            $this->create_update_form->get('shipping_address_info')->get('line_2')->setValue($this->user->getShippingAddress()->getLine2());
            $this->create_update_form->get('shipping_address_info')->get('city')->setValue($this->user->getShippingAddress()->getCity());
            $this->create_update_form->get('shipping_address_info')->get('state')->setValue($this->user->getShippingAddress()->getState());
            $this->create_update_form->get('shipping_address_info')->get('zipcode')->setValue($this->user->getShippingAddress()->getZipcode());
            $this->create_update_form->get('shipping_address_info')->get('phone')->setValue($this->user->getShippingAddress()->getPhone());
            $this->create_update_form->get('shipping_address_info')->get('company')->setValue($this->user->getShippingAddress()->getCompany());
        }
    }
}