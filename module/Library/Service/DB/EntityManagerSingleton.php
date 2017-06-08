<?php

namespace Library\Service\DB;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

/**
 * Represents the Doctrine entity manager and initializes the setup as a service.
 * Class EntityManagerSingleton
 * @package Library\Service\DB
 */
class EntityManagerSingleton implements FactoryInterface
{
    static protected $firstInstance = null;

    /**
     * Initializes the Doctrine entity manager
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return EntityManager
     * @throws \Doctrine\ORM\ORMException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Set up Doctrine entity manager
        $config = $serviceLocator->get('config');
        $paths = $config['doctrine']['entity_paths'];
        $isDevMode = $config['doctrine']['isDevMode'];

        $db_info = $config['db'];

        $setup = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);

        if ($_SERVER['APP_ENV'] == 'development' || $_SERVER['APP_ENV'] == 'vagrant' || $_SERVER['APP_ENV'] == 'localhost')
            $setup->setAutoGenerateProxyClasses(true);
        else
            $setup->setAutoGenerateProxyClasses(false);

        $setup->setProxyDir(getcwd() . '/proxies');

        return EntityManager::create($db_info, $setup);
    }

    /**
     * Returns the set instance of the entity manager
     * @return EntityManager
     */
    public static function getInstance()
    {
        return self::$firstInstance;
    }

    /**
     * Sets the global instance of the entity manager
     * @param EntityManager $firstInstance
     */
    public static function setInstance(EntityManager $firstInstance)
    {
        if (is_null(self::$firstInstance))
        {
            self::$firstInstance = $firstInstance;
        }
    }
}