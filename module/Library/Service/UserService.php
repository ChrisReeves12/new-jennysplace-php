<?php
/**
* The UserService class definition.
*
* The user service administers editing and updating users and customers
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\User\Address;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Mail\Transport\Sendmail;
use Zend\Mime\Message;
use Zend\Mime\Part;

/**
 * Class UserService
 * @package Library\Service
 */
class UserService extends AbstractService
{
    /** @var  AuthenticationService */
    protected $auth_service;

    /**
     * Set the authentication adapter to use
     */
    public function initialize()
    {
        $auth_adapter = $this->getServiceManager()->get('auth_adapter');
        $this->auth_service = new AuthenticationService();
        $this->auth_service->setAdapter($auth_adapter);
    }

    /**
     * @return AuthenticationService
     */
    public function getAuthService()
    {
        return $this->auth_service;
    }

    /**
     * Saves or updates a user
     *
     * @param array $data
     * @param User $user
     *
     * @return User
     * @throws \Exception
     */
    public function save($data, User $user = null)
    {
        // Get the entity manager
        $em = EntityManagerSingleton::getInstance();

        // Create or get the user
        if (!($user instanceof User))
        {
            $user = new User();
            $em->persist($user);

            // Check if user exists in database
            if ($em->getRepository('Library\Model\User\User')->findOneByEmail($data['basic_info']['email']) != null)
            {
                throw new \Exception("There is already a user registered under the email address " . $data['basic_info']['email']);
            }
        }

        if (!($user instanceof User))
        {
            throw new \Exception("The user being updated cannot be found in the database.");
        }

        // Set user attributes accordingly
        $user->setFirstName($data['basic_info']['first_name']);
        $user->setLastName($data['basic_info']['last_name']);
        $user->setEmail($data['basic_info']['email']);
        $user->setFax($data['basic_info']['fax']);
        $user->setTaxId($data['basic_info']['tax_id']);
        $user->setNewsletter($data['basic_info']['newsletter']);
        $user->setAdminMemo($data['basic_info']['admin_memo']);

        if (!empty($data['basic_info']['role']))
        {
            $user->setRole($data['basic_info']['role']);
        }
        else
        {
            // The role is set to customer by default
            if (is_null($user->getRole()))
            {
                $user->setRole('customer');
            }
        }

        if (isset($data['basic_info']['store_credit']))
        {
            $user->setStoreCredit($data['basic_info']['store_credit']);
        }

        if (!empty($data['basic_info']['password']))
        {
            $user->setPassword($data['basic_info']['password']);
        }

        if (!empty($data['basic_info']['status']))
        {
            $user->setStatus($data['basic_info']['status']);
        }
        else
        {
            // Set default status for new user being created
            if (is_null($user->getId()))
            {
                if (Settings::get('require_cust_validate') == '1')
                {
                    $user->setStatus(User::USER_STATUS_UNVERIFIED);
                }
                else
                {
                    $user->setStatus(User::USER_STATUS_VERIFIED);
                }
            }
        }

        // Set the billing address
        $billing_address = $user->getBillingAddress();
        if (!($billing_address instanceof Address))
        {
            $billing_address = new Address();
        }

        $billing_address->setCompany($data['billing_address_info']['company']);
        $billing_address->setFirstName($data['basic_info']['first_name']);
        $billing_address->setLastName($data['basic_info']['last_name']);
        $billing_address->setEmail($data['basic_info']['email']);
        $billing_address->setLine1($data['billing_address_info']['line_1']);
        $billing_address->setLine2($data['billing_address_info']['line_2']);
        $billing_address->setCity($data['billing_address_info']['city']);
        $billing_address->setState($data['billing_address_info']['state']);
        $billing_address->setZipcode($data['billing_address_info']['zipcode']);
        $billing_address->setPhone($data['billing_address_info']['phone']);
        $em->persist($billing_address);

        // Set up shipping address
        $shipping_address = $user->getShippingAddress();
        if (!($shipping_address instanceof Address))
        {
            $shipping_address = new Address();
        }

        // Check if shipping address is valid
        if (
            empty($data['shipping_address_info']['first_name']) || empty($data['shipping_address_info']['last_name']) ||
            empty($data['shipping_address_info']['line_1']) || empty($data['shipping_address_info']['city']) ||
            empty($data['shipping_address_info']['state']) || empty($data['shipping_address_info']['zipcode'])
        )
        {
            $skip_shipping_address = true;
        }
        else
        {
            $skip_shipping_address = false;
        }

        // Add addresses to the user
        if (!$skip_shipping_address)
        {
            $shipping_address->setCompany($data['shipping_address_info']['company']);
            $shipping_address->setFirstName($data['shipping_address_info']['first_name']);
            $shipping_address->setLastName($data['shipping_address_info']['last_name']);
            $shipping_address->setEmail($data['basic_info']['email']);
            $shipping_address->setLine1($data['shipping_address_info']['line_1']);
            $shipping_address->setLine2($data['shipping_address_info']['line_2']);
            $shipping_address->setCity($data['shipping_address_info']['city']);
            $shipping_address->setState($data['shipping_address_info']['state']);
            $shipping_address->setZipcode($data['shipping_address_info']['zipcode']);
            $shipping_address->setPhone($data['shipping_address_info']['phone']);
            $em->persist($shipping_address);

            $user->setShippingAddress($shipping_address);
        }

        $user->setBillingAddress($billing_address);

        // Finally save the user
        $em->persist($user);

        return $user;
    }

    /**
     * Sends verify email to user
     * @param array $data
     *
     * @return User
     */
    public function sendVerifyEmail($data)
    {
        $id = $data['id'];

        // Load user
        $em = EntityManagerSingleton::getInstance();
        $user = $em->getRepository('Library\Model\User\User')->findOneById($id);
        if ($user instanceof User)
        {
            // Create token for user
            $user->setToken(md5(time()));
            $em->flush($user);

            // Send email
            $token = $user->getToken();
            $store_info = Settings::getAll();

            // Create the email
            $message = new \Zend\Mail\Message();

            ob_start();
            ?>
            You are receiving this email because someone under this email registered for <?php echo $store_info['store_name']; ?>. If you have received this email in error,
            please contact us immediately at <?php echo $store_info['site_email']; ?>. Simply click the link below, and you will be taken to our website and your account will be verified:
            <br/><br/>

            <a href="<?php echo 'https://' . $store_info['site_url']; ?>/user/verify/<?php echo $token; ?>">
                <?php echo 'https://' . $store_info['site_url']; ?>/user/verify/<?php echo $token; ?>
            </a><br/><br/>

            On behalf of our staff and our associates, we'd like to welcome you to <?php echo $store_info['store_name']; ?>.
            We greatly appreciate your business, and look forward to doing business with you in the future!<br/><br/>
            Sincerly,<br/><br/>
            The <?php echo $store_info['store_name']; ?> Team
            <?php
            $email_message = ob_get_contents();
            ob_end_clean();

            $message->setTo($user->getEmail());
            $message->setFrom($store_info['site_email'], $store_info['store_name']);
            $message->setSubject($store_info['store_name'] . " Account Verification for " . $user->getFirstName() . ' ' . $user->getLastName());

            // Send email
            $html_email = new Part($email_message);
            $html_email->type = 'text/html';

            $mime_message = new Message();
            $mime_message->setParts([$html_email]);

            $message->setBody($mime_message);

            $transport = new Sendmail();
            $transport->send($message);
        }

        return $user;
    }

    /**
     * Encrypts a password.
     * @param string $password
     *
     * @return string
     */
    static public function encrypt_password($password)
    {
        return (md5(sha1($password)));
    }

    /**
     * Athenticates a user by email and password
     *
     * @param string $email
     * @param string $password
     *
     * @return User
     */
    public function authenticate($email, $password)
    {
        $em = $this->getServiceManager()->get('entity_manager');
        $this->getAuthService()->getAdapter()->setIdentity($email);
        $this->getAuthService()->getAdapter()->setCredential($password);

        $result = $this->getAuthService()->authenticate();

        // Check if authentication passed
        if ($result->getCode() == Result::SUCCESS)
        {
            $user_id = $result->getIdentity();
            $user = $em->getReference('Library\Model\User\User', $user_id);
        }
        else
        {
            $user = null;
        }

        return $user;
    }

    /**
     * Returns the currently logged in user or null if no user is logged in
     * @return User
     * @throws \Exception
     */
    public function getIdentity()
    {
        $em = $this->getServiceManager()->get('entity_manager');

        // Check if there is a user in session
        if ($this->getAuthService()->hasIdentity())
        {
            $user_id = $this->getAuthService()->getIdentity();
            $user = $em->getReference('Library\Model\User\User', $user_id);
            if ($user instanceof $user)
            {
                $result = $user;
            }
            else
            {
                throw new \Exception("Your account no longer exists in our database, please contact administrator.");
            }
        }
        else
        {
            $result = null;
        }

        return $result;
    }

    /**
     * Gets the IP address of the client
     * @return string
     */
    public function getClientIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    /**
     * Resets the user's password
     * @param array $data
     * @param User $user
     *
     * @throws \Exception
     */
    public function changePassword($data, User $user)
    {
        if ($data['password'] == '')
        {
            throw new \Exception("Please input a password.");
        }

        if ($data['password'] == $data['cpassword'])
        {
            $password = $data['password'];
            $user->setPassword($password);
            $user->setToken(null);
            EntityManagerSingleton::getInstance()->persist($user);
        }
        else
        {
            throw new \Exception("The password and the confirm password must match.");
        }
    }

    /**
     * Finds and sends password to email of the associated account
     * @param string $email
     * @return User
     */
    public function emailPassword($email)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $user = $em->getRepository('Library\Model\User\User')->findOneByEmail($email);

        if ($user instanceof User)
        {
            // Create token
            $token = md5(time());
            $user->setToken($token);
            $em->persist($user);
            $em->flush();

            // Get store information
            $store_info = Settings::getAll();

            // Create the email
            $message = new \Zend\Mail\Message();

            ob_start();
            ?>
            Hi there. You are receiving this email because a request was sent for a password reset
            on our website. You can reset your password for <?php echo $store_info['store_name']; ?> by clicking on the
            link below. You will be taken to our website, which will give you further in instruction on
            how to reset your password. If you have any questions or concerns, please reply to this email
            and tell us how we can assist you.<br/><br/>

            <a href="https://<?php echo $store_info['site_url']; ?>/auth/changepass/<?php echo $token; ?>">
                https://<?php echo $store_info['site_url']; ?>/auth/changepass/<?php echo $token; ?>
            </a><br/><br/>

            Thank you for being a member and shopping with <?php echo $store_info['store_name']; ?>!
            <?php
            $email_message = ob_get_contents();
            ob_end_clean();

            $message->setTo($user->getEmail());
            $message->setFrom($store_info['site_email'], $store_info['store_name']);
            $message->setSubject($store_info['store_name'] . " Password Recovery for " . $user->getFirstName() . ' ' . $user->getLastName());

            // Send email
            $html_email = new Part($email_message);
            $html_email->type = 'text/html';

            $mime_message = new Message();
            $mime_message->setParts([$html_email]);

            $message->setBody($mime_message);

            $transport = new Sendmail();
            $transport->send($message);

            return $user;
        }
        else
        {
            return null;
        }
    }
}