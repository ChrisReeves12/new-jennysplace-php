<?php
/**
 * The ServiceInterface class definition.
 *
 * All services called by the abstract service factory must
 * must implement this interface
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;

use Library\Model\AbstractModel;
use Zend\ServiceManager\ServiceLocatorInterface;

interface ServiceInterface
{
    /**
     * @param ServiceLocatorInterface $service_manager
     */
    public function setServiceManager(ServiceLocatorInterface $service_manager);

    /**
     * Initialize the service
     */
    public function initialize();

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceManager();

    /**
     * Deletes entities by an array of ids
     *
     * @param $ids[]
     * @param AbstractModel $entity
     */
    public function deleteByIds($ids, $entity);
}