<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\ServiceManager;
use Roave\PsrContainerDoctrine\EntityManagerFactory;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Standard config keys
$minimalConfig                                                    = require __DIR__ . '/minimal-config.php';
$dependencies                                                     = [];
$dependencies['factories']['doctrine.entity_manager.orm_default'] = EntityManagerFactory::class;
$dependencies['services']['config']                               = $minimalConfig;
$container                                                        = new ServiceManager($dependencies);

$object = $container->get('doctrine.entity_manager.orm_default');
if (is_object($object)) {
    echo $object::class . "\n"; // Doctrine\ORM\EntityManager
}

// Custom config keys
$customKeyConfig                                                     = require __DIR__ . '/minimal-config-custom-key.php';
$dependencies                                                        = [];
$dependencies['factories']['doctrine.entity_manager.orm_custom_key'] = [EntityManagerFactory::class, 'orm_custom_key'];
$dependencies['services']['config']                                  = $customKeyConfig;
$container                                                           = new ServiceManager($dependencies);

$object = $container->get('doctrine.entity_manager.orm_custom_key');
if (is_object($object)) {
    echo $object::class . "\n"; // Doctrine\ORM\EntityManager
}

// Full config
$fullConfig                         = require __DIR__ . '/full-config.php';
$dependencies                       = $fullConfig['dependencies'];
$dependencies['services']['config'] = $fullConfig;
$container                          = new ServiceManager($dependencies);

$object = $container->get(EntityManagerInterface::class);
if (is_object($object)) {
    echo $object::class . "\n"; // Doctrine\ORM\EntityManager
}
