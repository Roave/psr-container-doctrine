<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Migrations;

use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;

/**
 * @method ConfigurationLoader __invoke(ContainerInterface $container)
 */
class ConfigurationLoaderFactory extends AbstractFactory
{
    /**
     * @inheritDoc
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey)
    {
        $migrationsConfig = $this->retrieveConfig($container, $configKey, 'migrations');

        return new ConfigurationArray($migrationsConfig);
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultConfig(string $configKey) : array
    {
        return [
            'table_storage' => [
                'table_name' => 'migrations_executed',
                'version_column_name' => 'version',
                'version_column_length' => 255,
                'executed_at_column_name' => 'executed_at',
                'execution_time_column_name' => 'execution_time',
            ],
            'migrations_paths' => ['My\Migrations' => 'scripts/orm/migrations'],
            'all_or_nothing' => true,
            'check_database_platform' => true,
        ];
    }
}
