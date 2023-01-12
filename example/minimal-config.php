<?php

declare(strict_types=1);

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => ['url' => 'mysql://user:password@localhost/database'],
            ],
        ],
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => ['My\Entity' => 'my_entity'],
            ],
            'my_entity' => [
                'class' => XmlDriver::class,
                'paths' => __DIR__ . '/doctrine',
            ],
        ],
    ],
];

/**
* switch out the user and password with the correct connection string
* note that the my_entity driver you specified  is looking for entities written in xml files
* for entities written in php use the Annotation Driver (see full config)
*/
