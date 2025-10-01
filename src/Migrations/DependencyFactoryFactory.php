<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Migrations;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\EntityManagerFactory;

/** @extends AbstractFactory<DependencyFactory> */
final readonly class DependencyFactoryFactory extends AbstractFactory
{
    protected function createWithConfig(ContainerInterface $container, string $configKey): DependencyFactory
    {
        $entityManagerLoader = new ExistingEntityManager(
            $this->retrieveDependency(
                $container,
                $configKey,
                'entity_manager',
                EntityManagerFactory::class,
            ),
        );

        if ($container->has(ConfigurationLoader::class)) {
            $configurationLoader = $container->get(ConfigurationLoader::class);
        } else {
            $configurationLoader = (new ConfigurationLoaderFactory($configKey))($container);
        }

        return DependencyFactory::fromEntityManager(
            $configurationLoader,
            $entityManagerLoader,
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [];
    }
}
