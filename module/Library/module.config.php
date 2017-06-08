<?php
return [

    // Service Factories
    'service_manager' => [
        'invokables' => [
            'auth_adapter' => 'Library\Service\AuthAdapter'
        ],
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
            'Library\Service\AbstractService'
        ],
        'factories' => [
            'entity_manager' => 'Library\Service\DB\EntityManagerSingleton',
            'mongo_db' => 'Library\Service\DB\MongoDBClientSingleton',
            'db_connection' => 'Library\Service\DB\Connection',
        ]
    ],

     // Session settings
    'session_settings' => [
        'cache_expire'        => '10',
        'remember_me_seconds' => 864000,
        'cookie_lifetime'     => 864000,
        'use_cookies'         => true,
        'cookie_httponly'     => false,
        'name'                => \Library\Service\Settings::get('session_name')
    ],

    // View Helpers
    'view_helpers'    => [
        'invokables' => [
            'print_shipping_price'  => 'Library\ViewHelper\PrintShippingPrice',
        ]
    ]
];