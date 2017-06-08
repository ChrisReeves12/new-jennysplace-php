<?php
/**
 * The AbstractControllerFactory class definition.
 *
 * This class will auto-magically generate controllers for us
 * so that we don't have to keep declaring them in our config file.
 *
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Controller;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractControllerFactory implements AbstractFactoryInterface
{

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     *
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        // Check if the controller class is created
        return class_exists($requestedName . "Controller");
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     *
     * @return mixed
     * @throws \Exception
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        // Create the class
        $controller_class = $requestedName . 'Controller';
        $controller = new $controller_class();

        // Autowire this controller with its dependencies
        if ($controller instanceof JPController)
        {
            $controller->setEntityManager($serviceLocator->getServiceLocator()->get('entity_manager'));
            return $controller;
        }
        else
        {
            throw new \Exception("All controllers created with the AbstractControllerFactory must extend the JPController.");
        }
    }
}