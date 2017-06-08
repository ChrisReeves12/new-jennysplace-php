<?php
/**
* The AuthController class definition.
*
* This controller houses the log in/out functionality for authentication
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\Controller;

use Library\Controller\JPController;
use Library\Form\Auth\Login;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;
use Zend\Form\Form;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Class AuthController
 * @package Frontend\Controller
 */
class AuthController extends JPController
{
    protected $login_form;
    protected $forgot_pass_form;
    protected $logged_in;
    protected $user;

    /**
     * Log in user
     */
    public function indexAction()
    {
        // Check current login status
        $user_service = $this->getServiceLocator()->get('user');

        $user = $user_service->getIdentity();
        $viewModel = new ViewModel();

        if (!empty($user))
        {
            // Redirect to home as there is already a user in session
            return $this->redirect()->toRoute('home');
        }

        // Create forms
        $this->login_form = new Login();
        $this->forgot_pass_form = new Form();
        $this->forgot_pass_form->setAttribute('action', '/auth/forgotpassword');
        $this->forgot_pass_form->add([
            'name' => 'email',
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'Email Address',
                'class' => 'form-control'
            ]
        ]);

        // Handle post
        $login_result = $this->handle_post();

        // Process redirect to verify page if result is a redirect
        if ($login_result instanceof User)
        {
            if (false == $this->logged_in)
            {
                $user = $login_result;
                $viewModel->setTemplate('frontend/auth/verify');
            }

            // Redirect if user is logged in
            if (true == $this->logged_in)
            {
                // Send to the page they were on
                $whence = $_GET['whence'];

                if (!empty($whence))
                {
                    return $this->redirect()->toUrl(urldecode($whence));
                }
                else
                {
                    return $this->redirect()->toRoute('home');
                }
            }
        }

        // Add javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/frontend/verify.js');

        $viewModel->setVariables(['login_form' => $this->login_form, 'forgot_pass_form' => $this->forgot_pass_form, 'user' => $user, 'logged_in' => $this->logged_in]);
        return $viewModel;
    }

    /**
     * Send verify email
     * @return array
     * @throws \Exception
     */
    public function sendverifyAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $result = $user_service->sendVerifyEmail($data);
            if (!($result instanceof User))
            {
                throw new \Exception("Email failed to send. It seems there is no user loaded.");
            }

            return new JsonModel(['error' => false]);
        }
    }

    /**
     * The screen that helps to recover passwords
     * @return array
     */
    public function forgotpasswordAction()
    {
        $user_service = $this->getServiceLocator()->get('user');
        $email_sent = false;

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();

            $email = trim($data['email']);
            $email = strtolower($email);
            $user_service->emailPassword($email);
            $email_sent = true;
        }

        return ['email_sent' => $email_sent];
    }

    /**
     * The change password screen
     * @return array
     * @throws \Exception
     */
    public function changepassAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        $token = $this->params()->fromRoute('token');
        $em = EntityManagerSingleton::getInstance();
        $user = null;

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $user = $em->getRepository('Library\Model\User\User')->findOneByToken($token);

            if (!($user instanceof User))
            {
                throw new \Exception("The user cannot be found by the incoming token");
            }

            $user_service->changePassword($data, $user);
            $em->flush();
        }

        return ['user' => $user];
    }

    /**
     * Destroys the identity of the active user and logs the user out
     */
    public function logoutAction()
    {
        $user_service = $this->getServiceLocator()->get('user');
        $user_service->getAuthService()->clearIdentity();

        return $this->redirect()->toRoute('home');
    }

    /**
     * Handle post requests
     */
    public function handle_post()
    {
        $user_service = $this->getServiceLocator()->get('user');

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $task = $data['task'];
            unset($data['task']);

            // Handle post according to task
            switch ($task)
            {
                case 'login':

                    // Validate form
                    $this->login_form->setData($data);
                    if ($this->login_form->isValid())
                    {
                        $new_data = $this->login_form->getData();
                        $email = $new_data['email'];
                        $password = $new_data['password'];

                        $this->user = $user_service->authenticate($email, $password);

                        // Check if valid user was returned
                        if ($this->user instanceof User)
                        {
                            // Check if status is set and redirect to verify page if need be
                            if ($this->user->getStatus() == User::USER_STATUS_UNVERIFIED)
                            {
                                if (Settings::get('require_cust_validate') == 1)
                                {
                                    $this->logged_in = false;
                                    return $this->user;
                                }
                            }

                            $this->getServiceLocator()->get('session')->getSessionManager()->rememberMe();
                            $this->logged_in = true;
                        }
                        else
                        {
                            $this->logged_in = false;
                            $this->setErrorMessage("The email and/or password given does not match our records.");
                        }
                    }

                    break;
            }

            return $this->user;
        }
    }
}