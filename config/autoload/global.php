<?php

// Conditional database access settings
if ($_SERVER['APP_ENV'] == 'localhost')
{
    $db_host_info = ['driver' => 'pdo_mysql', 'host' => '127.0.0.1', 'dbname' => 'newjennysplace-development'];
}
elseif ($_SERVER['APP_ENV'] == 'development')
{
    $db_host_info = ['driver' => 'pdo_mysql', 'host' => '104.236.248.41', 'dbname' => 'newjennysplace-development'];
}
elseif ($_SERVER['APP_ENV'] == 'production')
{
    $db_host_info = ['driver' => 'pdo_mysql', 'host' => '127.0.0.1', 'dbname' => 'newjennysplace-master'];
}


return [

    'db' => $db_host_info,

    'doctrine' => [
        'entity_paths' => [
            __DIR__ . '/../../module/Library/Model'
        ],
        'isDevMode' => true
    ]
];
