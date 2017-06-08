<?php
/**
 * The AuthAdapter class definition.
 *
 * The adapter used to authenticate users
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;

use Library\Model\User\User;
use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Result;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthAdapter extends AbstractAdapter implements ServiceLocatorAwareInterface
{
    protected $service_manager;

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        // Authenticate the user
        $em = $this->getServiceLocator()->get('entity_manager');
        $email = $this->getIdentity();
        $password = UserService::encrypt_password($this->getCredential());

        // Attempt to find the user
        $user = $em->getRepository('Library\Model\User\User')->findOneBy(['email' => $email, 'password' => $password]);
        if ($user instanceof User)
        {
            $return = new Result(Result::SUCCESS, $user->getId());
        }
        else
        {
            $return = new Result(Result::FAILURE, $this->getIdentity());
        }

        return $return;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->service_manager = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->service_manager;
    }
}