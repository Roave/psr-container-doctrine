<?php

declare(strict_types=1);

use Roave\PsrContainerDoctrine\EntityManagerFactory;

// Standard config keys
$container = new PimpleInterop(null, [
    'doctrine.entity_manager.orm_default' => new EntityManagerFactory(),
]);

// Custom config keys
$container = new PimpleInterop(null, [
    'doctrine.entity_manager.orm_other' => new EntityManagerFactory('orm_other'),
]);
