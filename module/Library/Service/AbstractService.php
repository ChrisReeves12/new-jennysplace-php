<?php
/**
 * The AbstractService class definition.
 *
 * This class is the parent of all service classes
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;

use Library\Model\AbstractModel;
use Library\Service\DB\EntityManagerSingleton;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractService
 * @package Library\Service
 */
class AbstractService implements AbstractFactoryInterface, ServiceInterface
{
    protected $service_manager;

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     *
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $can_create_service = true;
        $class_name = 'Library\\Service\\' . ucfirst($requestedName) . 'Service';

        if (!class_exists($class_name))
        {
            $can_create_service = false;
        }
        else
        {
            // Check if class implements the correct interface
            if (!in_array('Library\Model\ServiceInterface', class_implements($class_name)))
                $can_create_service = true;
        }

        return $can_create_service;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     *
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $service_class_name = 'Library\\Service\\' . ucfirst($requestedName) . 'Service';
        $service_class = new $service_class_name();
        $service_class->setServiceManager($serviceLocator);

        // See if initialize returns an object
        if (!empty($return_value = $service_class->initialize()))
        {
            return $return_value;
        }

        return $service_class;
    }

    /**
     * @param ServiceLocatorInterface $service_manager
     */
    public function setServiceManager(ServiceLocatorInterface $service_manager)
    {
        $this->service_manager = $service_manager;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceManager()
    {
        return $this->service_manager;
    }

    /**
     * Deletes entities by an array of ids
     *
     * @param $ids[]
     * @param AbstractModel $entity
     */
    public function deleteByIds($ids, $entity)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get ids
        if (!is_array($ids))
        {
            $ids = explode(',', $ids);
        }

        $entity_class = get_class($entity);
        $entities = $em->getRepository($entity_class)->findBy(['id' => $ids]);

        if (count($entities) > 0)
        {
            foreach ($entities as &$entity)
            {
                if ($entity instanceof AbstractModel)
                {
                    // Delete the entity
                    $em->remove($entity);
                }
            }
        }
    }

    /**
     * Initializes the service
     */
    public function initialize()
    {

    }
}