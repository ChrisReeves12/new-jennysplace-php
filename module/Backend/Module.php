<?php
/**
 * The Backend module represents the administration side of the application.
 */

namespace Backend;

use Library\Model\User\User;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        // Set event to check for valid credentials when logging in
        $e->getViewModel()->setTemplate('frontend/layout');

        if (!isset($_SERVER['SHELL']))
        {
            $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, [$this, 'setup_backend'], 100);
        }
    }

    /**
     * Ensures that the correct layout view is used for the backend
     * @param MvcEvent $e
     */
    public function setup_backend(MvcEvent $e)
    {
        $user_service = $e->getApplication()->getServiceManager()->get('user');
        $routematch = $e->getRouteMatch();

        // Set correct template for each controller
        if ($routematch)
        {
            if (strstr($routematch->getParam('controller'), 'Backend'))
            {
                //Check if user is logged in and the role is correct
                $user = $user_service->getIdentity();
                if ($user instanceof User)
                {
                    if ($user->getRole() != 'administrator' && $user->getRole() != 'superuser')
                    {
                        $e->getResponse()->setStatusCode(404);
                    }
                    else
                    {
                        $e->getViewModel()->setTemplate('backend/layout');
                    }
                }
                else
                {
                    $e->getResponse()->setStatusCode(404);
                }
            }
        }
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__
                ]
            ]
        ];
    }

    public function getConfig()
    {
        return include __DIR__ . '/module.config.php';
    }
}
