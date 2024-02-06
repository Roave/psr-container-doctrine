<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\EntityManagerFactory;

use function sys_get_temp_dir;

final class EntityManagerFactoryTest extends TestCase
{
    public function testExtendsAbstractFactory(): void
    {
        self::assertInstanceOf(AbstractFactory::class, new EntityManagerFactory());
    }

    public function testDefaults(): void
    {
        $connection    = $this->createMock(Connection::class);
        $configuration = $this->buildConfiguration();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                ['doctrine.connection.orm_default', true],
                ['doctrine.configuration.orm_default', true],
            ]);

        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['doctrine.connection.orm_default', $connection],
                ['doctrine.configuration.orm_default', $configuration],
            ]);

        $factory       = new EntityManagerFactory();
        $entityManager = $factory($container);

        self::assertSame($connection, $entityManager->getConnection());
        self::assertSame($configuration, $entityManager->getConfiguration());
    }

    public function testConfigKeyTakenFromSelf(): void
    {
        $connection    = $this->createMock(Connection::class);
        $configuration = $this->buildConfiguration();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                ['doctrine.connection.orm_other', true],
                ['doctrine.configuration.orm_other', true],
            ]);
        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['doctrine.connection.orm_other', $connection],
                ['doctrine.configuration.orm_other', $configuration],
            ]);

        $factory       = new EntityManagerFactory('orm_other');
        $entityManager = $factory($container);

        self::assertSame($connection, $entityManager->getConnection());
        self::assertSame($configuration, $entityManager->getConfiguration());
    }

    public function testConfigKeyTakenFromConfig(): void
    {
        $connection    = $this->createMock(Connection::class);
        $configuration = $this->buildConfiguration();
        $config        = [
            'doctrine' => [
                'entity_manager' => [
                    'orm_default' => [
                        'connection' => 'orm_foo',
                        'configuration' => 'orm_bar',
                    ],
                ],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))
            ->method('has')
            ->willReturnMap([
                ['config', true],
                ['doctrine.connection.orm_foo', true],
                ['doctrine.configuration.orm_bar', true],
            ]);
        $container->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['config', $config],
                ['doctrine.connection.orm_foo', $connection],
                ['doctrine.configuration.orm_bar', $configuration],
            ]);

        $factory       = new EntityManagerFactory();
        $entityManager = $factory($container);

        self::assertSame($connection, $entityManager->getConnection());
        self::assertSame($configuration, $entityManager->getConfiguration());
    }

    private function buildConfiguration(): Configuration
    {
        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl(new MappingDriverChain());
        $configuration->setProxyDir(sys_get_temp_dir());
        $configuration->setProxyNamespace('EntityManagerFactoryTest');

        return $configuration;
    }
}
