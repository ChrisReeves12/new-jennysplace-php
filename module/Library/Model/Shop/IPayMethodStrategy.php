<?php
/**
* The IPayMethodStrategy interface definition.
*
* This interface describes a function that all payment methods should implement
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Interface IPayMethodStrategy
 * @package Library\Model\Shop
 */
interface IPayMethodStrategy
{

    /**
     * Processes the payment recieved
     *
     * @param array $info
     * @return array
     */
    public function process($info = []);

    /**
     * @param ServiceLocatorInterface $service_manager
     */
    public function setServiceManager(ServiceLocatorInterface $service_manager);
}