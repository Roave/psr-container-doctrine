<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;
use Roave\PsrContainerDoctrine\EntityManagerFactory;

require_once __DIR__ . '/../vendor/autoload.php';

// Standard config keys
$minimalConfig                       = require __DIR__ . '/minimal-config.php';
$minimalConfig['factories']          = [
    'doctrine.entity_manager.orm_default' => EntityManagerFactory::class,
];
$minimalConfig['services']['config'] = $minimalConfig;
$container                           = new ServiceManager($minimalConfig);

$object = $container->get('doctrine.entity_manager.orm_default');
if (is_object($object)) {
    echo $object::class . "\n"; // Doctrine\ORM\EntityManager
}

// Custom config keys
$minimalConfig                       = require __DIR__ . '/minimal-config-custom-key.php';
$minimalConfig['factories']          = [
    'doctrine.entity_manager.orm_custom_key' => [EntityManagerFactory::class, 'orm_custom_key'],
];
$minimalConfig['services']['config'] = $minimalConfig;
$container                           = new ServiceManager($minimalConfig);

$object = $container->get('doctrine.entity_manager.orm_custom_key');
if (is_object($object)) {
    echo $object::class . "\n"; // Doctrine\ORM\EntityManager
}
