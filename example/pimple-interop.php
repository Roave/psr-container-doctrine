<?php

use PimpleInterop;

// Standard config keys
$container = new PimpleInterop(null, [
    'doctrine.entity_manager.orm_default' => new \Roave\PsrContainerDoctrine\EntityManagerFactory(),
]);

// Custom config keys
$container = new PimpleInterop(null, [
    'doctrine.entity_manager.orm_other' => new \Roave\PsrContainerDoctrine\EntityManagerFactory('orm_other'),
]);
