<?php

use Laminas\ServiceManager\ServiceManager;

// Standard config keys
$container = new ServiceManager([
    'factories' => [
        'doctrine.entity_manager.orm_default' => \Roave\PsrContainerDoctrine\EntityManagerFactory::class,
    ],
]);

// Custom config keys
$container = new ServiceManager([
    'factories' => [
        'doctrine.entity_manager.orm_other' => [\Roave\PsrContainerDoctrine\EntityManagerFactory::class, 'orm_other'],
    ],
]);
