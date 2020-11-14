<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\ConfigurationLoaderFactory;

final class ConfigurationLoaderFactoryTest extends TestCase
{
    private const DIRECTORY = 'test/TestAsset';
    private const NAME      = 'Foo Bar';
    private const NS        = 'Acme\Lib\Migrations';
    private const TABLE     = 'baz';
    private const COLUMN    = 'bat';

    public function testExtendsAbstractFactory() : void
    {
        $this->assertInstanceOf(AbstractFactory::class, new ConfigurationLoaderFactory());
    }

    public function testConfigValues() : void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('getSchemaManager')
            ->willReturn($this->createMock(AbstractSchemaManager::class));
        $connection->method('getDatabasePlatform')
            ->willReturn($this->createMock(AbstractPlatform::class));

        $container = $this->createStub(ContainerInterface::class);

        $config = [
            'doctrine' => [
                'migrations' => [
                    'orm_default' => [
                        'directory' => self::DIRECTORY,
                        'name'      => self::NAME,
                        'namespace' => self::NS,
                        'table'     => self::TABLE,
                        'column'    => self::COLUMN,
                    ],
                ],
            ],
        ];

        $container->method('has')
            ->willReturnMap(
                [
                    ['config', true],
                    ['doctrine.connection.orm_default', true],
                ]
            );

        $container->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    ['doctrine.connection.orm_default', $connection],
                ]
            );

        $migrationsConfiguration = (new ConfigurationLoaderFactory())($container);

        $this->assertSame($connection, $migrationsConfiguration->getConnection());
        $this->assertSame(self::DIRECTORY, $migrationsConfiguration->getMigrationsDirectory());
        $this->assertSame(self::NAME, $migrationsConfiguration->getName());
        $this->assertSame(self::NS, $migrationsConfiguration->getMigrationsNamespace());
        $this->assertSame(self::TABLE, $migrationsConfiguration->getMigrationsTableName());
        $this->assertSame(self::COLUMN, $migrationsConfiguration->getMigrationsColumnName());
    }
}
