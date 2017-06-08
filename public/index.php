<?php
/**
 * New Jenny's Place
 * @copyright 2015 (c) by KPI & Company LLC
 * @author Christopher Reeves <chrisreeves12@yahoo.com>
 */

chdir(dirname(__DIR__));
date_default_timezone_set('America/New_York');

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
