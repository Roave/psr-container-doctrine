<?php

declare(strict_types=1);

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driver_class' => Driver::class,
                'wrapper_class' => PrimaryReadReplicaConnection::class,
                'params' => [
                    'primary' => ['url' => '//app:secr3t@primary.cluster:3306/foo?charset=utf8mb4'],
                    'replica' => [
                        ['url' => '//replica1.cluster:3306/foo?charset=utf8mb4'],
                        ['url' => '//replica2.cluster:3306/foo?charset=utf8mb4'],
                    ],
//                  Alternative setup:
//                    'primary' => [
//                        'user' => 'app',
//                        'password' => 'secr3t',
//                        'host' => 'primary.cluster',
//                        'port' => 3306,
//                        'dbname' => 'foo',
//                        'charset' => 'utf8mb4',
//                    ]
//                    'replica' => [
//                        [
//                            'host' => 'replica1.cluster',
//                            'port' => 3306,
//                            'dbname' => 'foo',
//                            'charset' => 'utf8mb4',
//                        ],
//                        [
//                            'host' => 'replica2.cluster',
//                            'port' => 3306,
//                            'dbname' => 'foo',
//                            'charset' => 'utf8mb4',
//                        ],
//                    ]
                ],
            ],
        ],
        'driver' => [
            'orm_default' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/Entity/',
                ],
            ],
        ],
    ],
];
