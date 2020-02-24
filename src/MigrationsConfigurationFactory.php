<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Migrations\Configuration\Configuration;
use Psr\Container\ContainerInterface;

/**
 * @method Configuration __invoke(ContainerInterface $container)
 */
class MigrationsConfigurationFactory extends AbstractFactory
{
    /**
     * @inheritDoc
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey)
    {
        $migrationsConfig = $this->retrieveConfig($container, $configKey, 'migrations_configuration');

        $configuration = new Configuration(
            $this->retrieveDependency(
                $container,
                $configKey,
                'connection',
                ConnectionFactory::class
            )
        );

        $configuration->setName($migrationsConfig['name']);
        $configuration->setMigrationsDirectory($migrationsConfig['directory']);
        $configuration->setMigrationsNamespace($migrationsConfig['namespace']);
        $configuration->setMigrationsTableName($migrationsConfig['table']);
        $configuration->registerMigrationsFromDirectory($migrationsConfig['directory']);
        $configuration->setMigrationsColumnName($migrationsConfig['column']);

        return $configuration;
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultConfig(string $configKey) : array
    {
        return [
            'directory' => 'scripts/doctrine-orm-migrations',
            'name'      => 'Doctrine Database Migrations',
            'namespace' => 'My\Migrations',
            'table'     => 'migrations',
            'column'    => 'version',
        ];
    }
}
