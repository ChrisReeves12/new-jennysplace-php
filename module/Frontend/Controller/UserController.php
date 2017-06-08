<?php
/**
* The UserController class definition.
*
* This controller houses the registration and user account pages
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\Controller;

use Library\Controller\JPController;
use Library\Form\OrderReturn\CustCreateUpdate;
use Library\Form\User\CreateUpdate;
use Library\Form\User\InputFilter\FilterSpec;
use Library\Model\Mail\Email;
use Library\Model\Shop\ProductReturn;
use Library\Model\Shop\ShopList\Order;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;
use Zend\InputFilter\Factory;
use Zend\View\Model\ViewModel;

/**
 * Class UserController
 * @package Frontend\Controller
 */
class UserController extends JPController
{
    protected $create_update_form;
    protected $user;

    /**
     * The registration form where users create new accounts
     * @return ViewModel
     */
    public function registerAction()
    {
        $session_service = $this->getServiceLocator()->get('session');
        $user_service = $this->getServiceLocator()->get('user');

        $userInfoViewModel = new ViewModel();
        $userInfoViewModel->setTemplate('element/user/userinfo');

        // Create user form
        $this->create_update_form = new CreateUpdate();

        // Build form
        $this->_build_form('register');

        // Handle posts
        $result = $this->_handle_post();

        // If a new user was registered, send user to confirmation page
        if ($result instanceof User)
        {
            $verifyView = new ViewModel();

            if (Settings::get('require_cust_validate') == 1)
            {
                if ($result->getStatus() == User::USER_STATUS_UNVERIFIED)
                {
                    // Show the user the page that makes them have to verify
                    $result = $user_service->sendVerifyEmail(['id' => $result->getId()]);
                    $verifyView->setTemplate('frontend/auth/verify');
                }
            }
            else
            {
                // Send user to home page
                $session_service->getContainer('auth')['user'] = $result->getId();
                $this->redirect()->toRoute('home');
            }

            $verifyView->setVariables(['user' => $result]);
            return $verifyView;
        }

        // Add variables to views
        $userInfoViewModel->setVariables(['create_update_form' => $this->create_update_form]);

        return ['user_info_view' => $userInfoViewModel];
    }

    /**
     * Verifies a user from an incoming email verification
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function verifyAction()
    {
        $token = $this->params()->fromRoute('param1');

        // Find user
        $em = EntityManagerSingleton::getInstance();
        $user = $em->getRepository('Library\Model\User\User')->findOneByToken($token);
        if (!($user instanceof User) || empty($token))
        {
            throw new \Exception("The user cannot be found from the token.");
        }

        // Update status and delete the token
        $user->setStatus(User::USER_STATUS_VERIFIED);
        $user->setToken(null);
        $em->flush($user);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('frontend/user/confirmuser');
        $viewModel->setVariables(['user' => $user]);

        return $viewModel;
    }

    /**
     * The account screen where customers can administer their account
     * @return ViewModel
     */
    public function accountAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        // Check if user is logged in
        $this->user = $user_service->getIdentity();
        if (!($this->user instanceof User))
        {
            return $this->redirect()->toRoute('auth');
        }

        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Create view models
        $user_info_forms = new ViewModel();
        $order_list = new ViewModel();
        $user_info_forms->setTemplate('element/user/userinfo');
        $order_list->setTemplate('element/user/orderlist');

        // Get basic and address forms
        $this->create_update_form = new CreateUpdate();

        // Build form
        $this->_build_form('account');
        $this->_hydrate_form();

        // Get information on orders
        $orders = $em->getRepository('Library\Model\Shop\ShopList\Order')->findBy(['user' => $this->user]);

        // Get returns
        $returns = $em->getRepository('Library\Model\Shop\ProductReturn')->findBy(['user' => $this->user], ['date_created' => 'DESC']);

        // Handle posts
        $this->_handle_post();

        // Add variables to views
        $user_info_forms->setVariables(['create_update_form' => $this->create_update_form]);
        $order_list->setVariables(['orders' => $orders]);

        return ['user' => $this->user,
            'returns' => $returns,
            'order_list' => $order_list,
            'user_info_forms' => $user_info_forms];
    }

    /**
     * The page that allows the customer to view an order they placed
     * @return array
     * @throws \Exception
     */
    public function orderAction()
    {
        // Check if user is logged in
        $user_service = $this->getServiceLocator()->get('user');
        $this->user = $user_service->getIdentity();
        if (!($this->user instanceof User))
        {
            return $this->redirect()->toRoute('auth');
        }

        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get order
        $order_num = $this->params()->fromRoute('param1');
        $order = $em->getRepository('Library\Model\Shop\ShopList\Order')->findOneBy(['order_number' => $order_num, 'user' => $this->user]);
        if (!($order instanceof Order))
        {
            throw new \Exception("The order you are trying to view cannot be found.");
        }

        return ['order' => $order];
    }

    /**
     * Handles submitting of return requests
     * @return array|\Zend\Http\Response
     */
    public function returnAction()
    {
        $order_service = $this->getServiceLocator()->get('order');
        $user_service = $this->getServiceLocator()->get('user');

        // Check if user is logged in
        $user = $user_service->getIdentity();
        if (!($user instanceof User))
        {
            return $this->redirect()->toRoute('auth');
        }

        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $line_id = $this->params()->fromRoute('param1');
        $shop_list_element = $em->getRepository('Library\Model\Shop\ShopListElement')->findOneById($line_id);

        // Find a matching return for the object
        $return = $em->getRepository('Library\Model\Shop\ProductReturn')->findOneBy(['user' => $user, 'shop_list_element' => $shop_list_element]);
        $cust_create_update = new CustCreateUpdate();

        if ($return instanceof ProductReturn)
        {
            $cust_create_update->get('message')->setValue($return->getCustomerMessage());
            $cust_create_update->get('admin_message')->setValue($return->getAdminMessage());
        }

        // Handle posts
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $cust_create_update->setData($data);

            if ($cust_create_update->isValid())
            {
                $new_data = $cust_create_update->getData();
                $order_service->saveOrderReturn($new_data, $return, $shop_list_element, $user);
                $em->flush();

                $this->flashMessenger()->addMessage("Thank you, your return request for this product has been sent and you will be contacted.");
            }
        }

        return ['return' => $return, 'cust_create_update' => $cust_create_update, 'shop_list_element' => $shop_list_element];
    }

    /**
     * Handle incoming posts
     * @throws \Exception
     */
    private function _handle_post()
    {
        $user_service = $this->getServiceLocator()->get('user');

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $task = $data['task'];
            unset($data['task']);

            switch ($task)
            {
                case 'create_update':

                    $this->create_update_form->setData($data);
                    if ($this->create_update_form->isValid())
                    {
                        $valid_data = $this->create_update_form->getData();

                        if ($valid_data['basic_info']['password'] != $valid_data['basic_info']['cpassword'])
                        {
                            $this->create_update_form->get('basic_info')->get('password')->setMessages(['The password and the confirm password must match.']);
                            break;
                        }

                        // Check if there is a user with this email
                        $em = EntityManagerSingleton::getInstance();
                        $logged_in_user = $user_service->getIdentity();

                        $other_user = $em->getRepository('Library\Model\User\User')->findOneBy(['email' => $valid_data['basic_info']['email']]);
                        if ($other_user instanceof User)
                        {
                            if ($logged_in_user instanceof User)
                            {
                                if ($other_user->getId() != $logged_in_user->getId())
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

                        $user = $user_service->save($valid_data, $this->user);

                        EntityManagerSingleton::getInstance()->flush();
                        return $user;
                    }
                    break;
            }
        }

        return null;
    }

    /**
     * Contact form for site
     */
    public function contactAction()
    {
        // Handle contact form submission
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();

            // Create input filter
            $input_filter_factory = new Factory();
            $input_filter = $input_filter_factory->createInputFilter([
                [
                    'name' => 'email',
                    'required' => 'false',
                    'filters' => [
                        ['name' => 'StringTrim'],
                        ['name' => 'StripTags']
                    ],
                    'validators' => [
                        ['name' => 'EmailAddress']
                    ]
                ],
                [
                    'name' => 'subject',
                    'required' => 'false',
                    'filters' => [
                        ['name' => 'StringTrim'],
                        ['name' => 'StripTags']
                    ]
                ],
                [
                    'name' => 'content',
                    'required' => 'false',
                    'filters' => [
                        ['name' => 'StringTrim'],
                        ['name' => 'StripTags']
                    ]
                ]
            ]);

            // Validate the input
            $input_filter->setData($data);

            if ($input_filter->isValid())
            {
                $this->setSuccessMessage("Thank you for your message. We will contact you regarding your inquiry as soon as possible.<br/>You can normally expect a response within 24 to 48 business hours.");
                $mail_service = $this->getServiceLocator()->get("mailer");
                $email = new Email();
                $email->setFrom($input_filter->getValue("email"));
                $email->setSubject($input_filter->getValue("subject"));
                $email->setMessage($input_filter->getValue("content"));

                $mail_service->processEmail($email, "info@newjennysplace.com");
            }
            else
            {
                // Validation failed
                $this->setErrorMessage("Please make sure all the fields are filled in and that the email address provided is valid.");
            }

        }
        return [];
    }

    /**
     * Build and setup form and validators
     *
     * @param $type
     */
    private function _build_form($type)
    {
        if ($type == 'register')
        {
            // Remove name fields for billing address
            $this->create_update_form->get('billing_address_info')->remove('first_name');
            $this->create_update_form->get('billing_address_info')->remove('last_name');
            $this->create_update_form->get('submit')->setAttribute('value', 'Register');

            // Setup filter and validation specifications
            $this->create_update_form->setupFilterSpecs('basic_info', FilterSpec::getFrontendBasicInfoSpec());
            $this->create_update_form->setupFilterSpecs('billing_address_info', FilterSpec::getBillingAddressSpec());
            $this->create_update_form->setupFilterSpecs('shipping_address_info', FilterSpec::getShippingAddressSpec());
        }
        elseif ($type == 'account')
        {
            // Remove name fields for billing address
            $this->create_update_form->get('billing_address_info')->remove('first_name');
            $this->create_update_form->get('billing_address_info')->remove('last_name');
            $this->create_update_form->get('submit')->setAttribute('value', 'Save Contact Information');

            // Setup filter and validation specifications
            $this->create_update_form->setupFilterSpecs('basic_info', FilterSpec::getFrontendAccountBasicInfoSpec());
            $this->create_update_form->setupFilterSpecs('billing_address_info', FilterSpec::getBillingAddressSpec());
            $this->create_update_form->setupFilterSpecs('shipping_address_info', FilterSpec::getShippingAddressSpec());
        }
    }

    /**
     * Fill up the form with customer information
     */
    private function _hydrate_form()
    {
        // Hydrate
        $this->create_update_form->get('basic_info')->get('first_name')->setValue($this->user->getFirstName());
        $this->create_update_form->get('basic_info')->get('last_name')->setValue($this->user->getLastName());
        $this->create_update_form->get('basic_info')->get('email')->setValue($this->user->getEmail());
        $this->create_update_form->get('basic_info')->get('newsletter')->setValue($this->user->getNewsletter());

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