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
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->willReturnMap(
                [
                    [ConfigurationLoader::class, true],
                    ['doctrine.entity_manager.orm_default', true],
                ]
            );

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $container->method('get')
            ->willReturnMap(
                [
                    [ConfigurationLoader::class, new ConfigurationArray([])],
                    ['doctrine.entity_manager.orm_default', $entityManager],
                ]
            );

        $factory           = new DependencyFactoryFactory();
        $dependencyFactory = $factory($container);
        self::assertInstanceOf(DependencyFactory::class, $dependencyFactory);
        self::assertSame($entityManager, $dependencyFactory->getEntityManager());
        self::assertInstanceOf(Configuration::class, $dependencyFactory->getConfiguration());
    }
}
