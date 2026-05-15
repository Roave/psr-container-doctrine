<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\Migrations;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Migrations\DependencyFactoryFactory;

final class DependencyFactoryFactoryTest extends TestCase
{
    public function testCanCreateDependencyFactory(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnMap(
                [
                    [ConfigurationLoader::class, true],
                    ['doctrine.entity_manager.orm_default', true],
                ],
            );

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $container->method('get')
            ->willReturnMap(
                [
                    [ConfigurationLoader::class, new ConfigurationArray([])],
                    ['doctrine.entity_manager.orm_default', $entityManager],
                ],
            );

        $factory           = new DependencyFactoryFactory();
        $dependencyFactory = $factory($container);
        self::assertInstanceOf(DependencyFactory::class, $dependencyFactory);
        self::assertSame($entityManager, $dependencyFactory->getEntityManager());
        self::assertInstanceOf(Configuration::class, $dependencyFactory->getConfiguration());
    }

    public function testInstantiatesConfigurationLoaderFactoryWhenNotInContainer(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $config    = [
            'doctrine' => [
                'configuration' => [
                    'orm_default' => [
                        'metadata_cache' => 'metadata',
                        'result_cache' => 'result',
                        'query_cache' => 'query',
                        'hydration_cache' => 'hydration',
                        'second_level_cache' => ['enabled' => true],
                    ],
                ],
            ],
        ];

        $container->method('has')
            ->willReturnMap(
                [
                    ['config', true],
                    [ConfigurationLoader::class, false],
                    ['doctrine.entity_manager.orm_default', true],
                ],
            );

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $container->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    ['doctrine.entity_manager.orm_default', $entityManager],
                ],
            );

        $factory           = new DependencyFactoryFactory();
        $dependencyFactory = $factory($container);
        self::assertInstanceOf(DependencyFactory::class, $dependencyFactory);
        self::assertSame($entityManager, $dependencyFactory->getEntityManager());
        self::assertInstanceOf(Configuration::class, $dependencyFactory->getConfiguration());
    }
}
