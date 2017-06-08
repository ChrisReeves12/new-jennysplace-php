<?php
/**
 * The Console module represents the functionality of command prompt access.
 */

namespace Console;


class Module
{
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

    public function getConfig()
    {
        return include __DIR__ . '/module.config.php';
    }
}
