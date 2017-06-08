<?php
/**
 * The Library module contains the domain model and other classes the application uses.
 */

namespace Library;

use Doctrine\ORM\Events;
use Library\EventListener\Product\ProductEventListener;
use Library\EventListener\Product\SkuEventListener;
use Library\EventListener\ShopList\ShopListEventListener;
use Library\Service\DB\EntityManagerSingleton;
use Zend\Log\Logger;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->_init($e);
        $this->_start_events($e);
    }

    public function getConfig()
    {
        return include __DIR__ . '/module.config.php';
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

    /**
     * Initialize the system
     * @param MvcEvent $e
     */
    private function _init(MvcEvent $e)
    {
        // Set up global instances
        EntityManagerSingleton::setInstance($e->getApplication()->getServiceManager()->get('entity_manager'));

        // Initialize everything
        if ($_SERVER['APP_ENV'] != 'production')
        {
            register_shutdown_function([$this, 'handle_shutdown']);
        }
        else
        {
            // Log errors to file in production
            $logger = $e->getApplication()->getServiceManager()->get('logger');
            Logger::registerErrorHandler($logger);
            Logger::registerFatalErrorShutdownFunction($logger);
        }

        error_reporting(~E_ALL);

        // Set up sessions
        $session_manager = $e->getApplication()->getServiceManager()->get('session')->getSessionManager();
        $session_manager->start();
        Container::setDefaultManager($session_manager);
    }

    /**
     * Set events up
     * @param MvcEvent $e
     */
    private function _start_events(MvcEvent $e)
    {
        // Set up event listeners
        $eventManager = $e->getApplication()->getServiceManager()->get('entity_manager')->getEventManager();
        $eventManager->addEventListener([Events::preUpdate, Events::onFlush, Events::prePersist, Events::postFlush, Events::preRemove], new ShopListEventListener($e->getApplication()->getServiceManager()));
        $eventManager->addEventListener([Events::preUpdate, Events::prePersist, Events::postFlush], new SkuEventListener($e->getApplication()->getServiceManager()));
        $eventManager->addEventListener([Events::preUpdate, Events::prePersist], new ProductEventListener($e->getApplication()->getServiceManager()));

        // Handle errors
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'handle_exceptions'], 100);
    }

    /**
     * Handle fatal errors that shutdown the request
     */
    public function handle_shutdown()
    {
        $error = error_get_last();

        // Handle errors
        $this->show_error_message($error);
    }

    /**
     * Shows an error message the appropriate way based on the request type
     * @param $error
     */
    public function show_error_message($error)
    {
        if (!empty($error))
        {
            if (in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]))
            {
                if (!isset($_SERVER['SHELL']))
                {
                    if (strstr($_SERVER['HTTP_ACCEPT'], 'application/json'))
                    {
                        // Form error message for popups in JSON actions
                        $message = "Error: " . $error['message'] . "\nFile: " . $error['file'] . "\nLine: " . $error['line'];
                        exit(json_encode(['error' => true, 'message' => $message]));
                    }
                    else
                    {
                        // Show error message on pages
                        $error_element = <<<EOD
<table style="border: 1px solid black; border-collapse: collapse; font-size: 14px; font-family: arial;">
    <tr style="background-color: #8E8EC7; color: white;">
        <th> </th>
        <th padding: 3px;>An Error Occured</th>
    </tr>
    <tr><td style="padding: 3px; border: 1px solid black;">Message</td><td style="padding: 3px; border: 1px solid black;">{$error['message']}</td></tr>
    <tr><td style="padding: 3px; border: 1px solid black;">File</td><td style="padding: 3px; border: 1px solid black;">{$error['file']}</td></tr>
    <tr><td style="padding: 3px; border: 1px solid black;">Line</td><td style="padding: 3px; border: 1px solid black;">{$error['line']}</td></tr>
</table>
EOD;
echo $error_element;
                    }
                }
                else
                {
                    // Command line error
                    $message = "Error: " . $error['message'] . "\nFile: " . $error['file'] . "\nLine: " . $error['line'];
                    echo $message;
                    exit(1);
                }
            }
        }
    }

    /**
     * Handle exceptions coming in through AJAX
     * @param MvcEvent $e
     */
    public function handle_exceptions(MvcEvent $e)
    {
        $ex = $e->getParam('exception');

        // Handle JSON errors
        if (!isset($_SERVER['SHELL']))
        {
            if (strstr($_SERVER['HTTP_ACCEPT'], 'application/json'))
            {
                exit(json_encode(['error' => true, 'message' => $ex->getMessage()]));
            }
        }
    }
}
