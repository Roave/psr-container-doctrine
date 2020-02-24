<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\MigrationsConfigurationFactory;

class MigrationsConfigurationFactoryTest extends TestCase
{
    public function testExtendsAbstractFactory() : void
    {
        $this->assertInstanceOf(AbstractFactory::class, new MigrationsConfigurationFactory());
    }

    public function testConfigValues() : void
    {
        $connection = $this->buildConnection();
        $container  = $this->createMock(ContainerInterface::class);

        $config = [
            'doctrine' => [
                'migrations_configuration' => [
                    'orm_default' => [
                        'directory' => 'test/TestAsset',
                        'name'      => 'Foo Bar',
                        'namespace' => 'Acme\Lib\Migrations',
                        'table'     => 'baz',
                        'column'    => 'ver',
                    ],
                ],
            ],
        ];

        $container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['config'], ['doctrine.connection.orm_default'])
            ->willReturnOnConsecutiveCalls(true, true);

        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['config'], ['doctrine.connection.orm_default'])
            ->willReturnOnConsecutiveCalls($config, $connection);

        $migrationsConfiguration = (new MigrationsConfigurationFactory())($container);

        $this->assertSame($connection, $migrationsConfiguration->getConnection());
        $this->assertSame(
            $config['doctrine']['migrations_configuration']['orm_default']['directory'],
            $migrationsConfiguration->getMigrationsDirectory()
        );
        $this->assertSame(
            $config['doctrine']['migrations_configuration']['orm_default']['name'],
            $migrationsConfiguration->getName()
        );
        $this->assertSame(
            $config['doctrine']['migrations_configuration']['orm_default']['namespace'],
            $migrationsConfiguration->getMigrationsNamespace()
        );
        $this->assertSame(
            $config['doctrine']['migrations_configuration']['orm_default']['table'],
            $migrationsConfiguration->getMigrationsTableName()
        );
        $this->assertSame(
            $config['doctrine']['migrations_configuration']['orm_default']['column'],
            $migrationsConfiguration->getMigrationsColumnName()
        );
    }

    private function buildConnection() : Connection
    {
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $platform      = $this->createMock(AbstractPlatform::class);
        $connection    = $this->createPartialMock(Connection::class, ['getSchemaManager', 'getDatabasePlatform']);
        $connection->method('getSchemaManager')->willReturn($schemaManager);
        $connection->method('getDatabasePlatform')->willReturn($platform);

        return $connection;
    }
}
