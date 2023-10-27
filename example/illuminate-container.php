<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Roave\PsrContainerDoctrine\EntityManagerFactory;

require_once __DIR__ . '/../vendor/autoload.php';

// Standard config keys
$minimalConfig = require __DIR__ . '/minimal-config.php';
$container     = new Container();
$container->bind('config', static fn () => $minimalConfig);
$container->bind('doctrine.entity_manager.orm_default', Closure::fromCallable(new EntityManagerFactory()));

$object = $container->get('doctrine.entity_manager.orm_default');
if (is_object($object)) {
    echo $object::class . "\n"; // Doctrine\ORM\EntityManager
}

// Custom config keys
$minimalConfig = require __DIR__ . '/minimal-config-custom-key.php';
$container     = new Container();
$container->bind('config', static fn () => $minimalConfig);
$container->bind('doctrine.entity_manager.orm_custom_key', Closure::fromCallable(new EntityManagerFactory('orm_custom_key')));

$object = $container->get('doctrine.entity_manager.orm_custom_key');
if (is_object($object)) {
    echo $object::class . "\n"; // Doctrine\ORM\EntityManager
}
