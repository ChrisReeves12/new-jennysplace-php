<?php
/**
 * The MongoDBClientSingleton class definition.
 *
 * Service representing Mongo DB connection
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service\DB;

use MongoDB\Client as MongoDBClient;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MongoDBClientSingleton
 * @package Library\Service\DB
 */
class MongoDBClientSingleton implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return MongoDBClient
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MongoDBClient();
    }}