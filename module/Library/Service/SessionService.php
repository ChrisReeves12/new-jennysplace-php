<?php
/**
* The SessionService class definition.
*
* This class represents the session to save data between requests
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;

/**
 * Class SessionService
 * @package Library\Service
 */
class SessionService extends AbstractService
{
    static private $containers = [];

    protected $session_manager;

    /**
     * Create service to produce a session object
     */
    public function initialize()
    {
        $serviceLocator = $this->getServiceManager();
        $session_settings = $serviceLocator->get('config')['session_settings'];
        $session_config = new SessionConfig();
        $session_config->setOptions($session_settings);

        // Create the session
        $session_manager = new SessionManager($session_config);
        $this->setSessionManager($session_manager);
    }

    /**
     * @return SessionManager
     */
    public function getSessionManager()
    {
        return $this->session_manager;
    }

    /**
     * @param SessionManager $session_manager
     */
    public function setSessionManager($session_manager)
    {
        $this->session_manager = $session_manager;
    }

    /**
     * Returns a session container for storage
     *
     * @param $container_name
     * @return Container
     */
    public function getContainer($container_name)
    {
        if (empty($this->containers[$container_name]))
        {
            $this->containers[$container_name] = new Container($container_name);
        }

        return $this->containers[$container_name];
    }
}