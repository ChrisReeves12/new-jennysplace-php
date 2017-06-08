<?php
/**
 * The LoggerService class definition.
 *
 * This service allows us to log things that we need to refer to later
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream as StreamWriter;

/**
 * Class LoggerService
 * @package Library\Service
 */
class LoggerService extends AbstractService
{
    protected $logger;

    /**
     * Set up logger and return it.
     * By default it logs errors but can be used to log other things.
     */
    public function initialize()
    {
        $this->logger = new Logger();
        $this->logger->addWriter(new StreamWriter(getcwd() . '/error.log'));

        return $this->logger;
    }
}