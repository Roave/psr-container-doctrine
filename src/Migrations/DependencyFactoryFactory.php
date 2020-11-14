<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Migrations;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\EntityManagerFactory;

final class DependencyFactoryFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey)
    {
        $entityManagerLoader = new ExistingEntityManager(
            $this->retrieveDependency(
                $container,
                $configKey,
                'entity_manager',
                EntityManagerFactory::class
            )
        );

        return DependencyFactory::fromEntityManager(
            $container->get(ConfigurationLoader::class),
            $entityManagerLoader
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig(string $configKey) : array
    {
        return [];
    }
}
