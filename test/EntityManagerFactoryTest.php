<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\Common\EventManager;
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
    public function testExtendsAbstractFactory() : void
    {
        $this->assertInstanceOf(AbstractFactory::class, new EntityManagerFactory());
    }

    public function testDefaults() : void
    {
        $connection    = $this->buildConnection();
        $configuration = $this->buildConfiguration();

        $container = $this->getMockBuilder(ContainerInterface::class)->onlyMethods(['has', 'get'])->getMock();
        $container->expects($this->exactly(3))
            ->method('has')
            ->withConsecutive(['config'], ['doctrine.connection.orm_default'], ['doctrine.configuration.orm_default'])
            ->willReturnOnConsecutiveCalls(false, true, true);

        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['doctrine.connection.orm_default'], ['doctrine.configuration.orm_default'])
            ->willReturnOnConsecutiveCalls($connection, $configuration);

        $factory       = new EntityManagerFactory();
        $entityManager = $factory($container);

        $this->assertSame($connection, $entityManager->getConnection());
        $this->assertSame($configuration, $entityManager->getConfiguration());
    }

    public function testConfigKeyTakenFromSelf() : void
    {
        $connection    = $this->buildConnection();
        $configuration = $this->buildConfiguration();

        $container = $this->getMockBuilder(ContainerInterface::class)->onlyMethods(['has', 'get'])->getMock();
        $container->expects($this->exactly(3))
            ->method('has')
            ->withConsecutive(['config'], ['doctrine.connection.orm_other'], ['doctrine.configuration.orm_other'])
            ->willReturnOnConsecutiveCalls(false, true, true);
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['doctrine.connection.orm_other'], ['doctrine.configuration.orm_other'])
            ->willReturnOnConsecutiveCalls($connection, $configuration);

        $factory       = new EntityManagerFactory('orm_other');
        $entityManager = $factory($container);

        $this->assertSame($connection, $entityManager->getConnection());
        $this->assertSame($configuration, $entityManager->getConfiguration());
    }

    public function testConfigKeyTakenFromConfig() : void
    {
        $connection    = $this->buildConnection();
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

        $container = $this->getMockBuilder(ContainerInterface::class)->onlyMethods(['has', 'get'])->getMock();
        $container->expects($this->exactly(3))
            ->method('has')
            ->withConsecutive(['config'], ['doctrine.connection.orm_foo'], ['doctrine.configuration.orm_bar'])
            ->willReturnOnConsecutiveCalls(true, true, true);
        $container->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['doctrine.connection.orm_foo'], ['doctrine.configuration.orm_bar'])
            ->willReturnOnConsecutiveCalls($config, $connection, $configuration);

        $factory       = new EntityManagerFactory();
        $entityManager = $factory($container);

        $this->assertSame($connection, $entityManager->getConnection());
        $this->assertSame($configuration, $entityManager->getConfiguration());
    }

    private function buildConnection() : Connection
    {
        $eventManager = $this->createStub(EventManager::class);
        $connection   = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEventManager'])
            ->getMock();
        $connection->method('getEventManager')->willReturn($eventManager);

        return $connection;
    }

    private function buildConfiguration() : Configuration
    {
        $configuration = new Configuration();
        /** @psalm-suppress InvalidArgument - some funky stuff going on with the BC shim from Doctrine here... */
        $configuration->setMetadataDriverImpl(new MappingDriverChain());
        $configuration->setProxyDir(sys_get_temp_dir());
        $configuration->setProxyNamespace('EntityManagerFactoryTest');

        return $configuration;
    }
}
