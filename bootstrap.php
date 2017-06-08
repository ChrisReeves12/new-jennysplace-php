<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$paths = [
    __DIR__ . "/module/Library/Model"
];
$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);

// database configuration parameters
$conn = [
    'driver' => 'pdo_mysql',
    'user' => 'root',
    'password' => 'root',
    'host' => '127.0.0.1',
    'dbname' => 'ms-rebuild'
];

// obtaining the entity manager
$em = EntityManager::create($conn, $config);