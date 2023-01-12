<?php

declare(strict_types=1);

use App\Doctrine\CustomCacheProvider;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\ConfigurationLoaderFactory;
use Roave\PsrContainerDoctrine\Migrations\CommandFactory;
use Roave\PsrContainerDoctrine\Migrations\DependencyFactoryFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'result_cache' => 'array',
                'metadata_cache' => 'filesystem',
                'query_cache' => 'array',
                'hydration_cache' => 'array',
                'driver' => 'orm_default', // Actually defaults to the configuration config key, not hard-coded
                'auto_generate_proxy_classes' => true,
                'proxy_dir' => 'data/cache/DoctrineEntityProxy',
                'proxy_namespace' => 'DoctrineEntityProxy',
                'entity_namespaces' => [],
                'datetime_functions' => [],
                'string_functions' => [],
                'numeric_functions' => [],
                'filters' => [],
                'named_queries' => [],
                'named_native_queries' => [],
                'custom_hydration_modes' => [],
                'naming_strategy' => null,
                'quote_strategy' => null,
                'default_repository_class_name' => null,
                'repository_factory' => null,
                'class_metadata_factory_name' => null,
                'entity_listener_resolver' => null,
                'second_level_cache' => [
                    'enabled' => false,
                    'default_lifetime' => 3600,
                    'default_lock_lifetime' => 60,
                    'file_lock_region_directory' => '',
                    'regions' => [],
                ],
                'sql_logger' => null,
                'middlewares' => [
                    // List of middlewares doctrine will use to decorate the `Driver` component.
                    // (see https://github.com/doctrine/dbal/blob/3.3.2/docs/en/reference/architecture.rst#middlewares)
                    'app.foo.middleware', // Will be looked up in the container.
                    'app.bar.middleware', // Will be looked up in the container.
                ],
            ],
        ],
        'connection' => [
            'orm_default' => [
                'driver_class' => Driver::class,
                'wrapper_class' => null,
                'pdo' => null,
                'configuration' => 'orm_default', // Actually defaults to the connection config key, not hard-coded
                'event_manager' => 'orm_default', // Actually defaults to the connection config key, not hard-coded
                'params' => [],
                'doctrine_mapping_types' => [],
                'doctrine_commented_types' => [],
            ],
        ],
        'entity_manager' => [
            'orm_default' => [
                'connection' => 'orm_default', // Actually defaults to the entity manager config key, not hard-coded
                'configuration' => 'orm_default', // Actually defaults to the entity manager config key, not hard-coded
            ],
        ],
        'event_manager' => [
            'orm_default' => [
                'subscribers' => [],
                'listeners' => [],
            ],
        ],
        'driver' => [
            'orm_default' => [
                'class' => null,
                'paths' => [],
                'extension' => null,
                'drivers' => [],
                'global_basename' => null,
                'default_driver' => null,
            ],
        ],
        'cache' => [
            'array' => [
                'class' => ArrayAdapter::class,
            ],
            'filesystem' => [
                'class' => FilesystemAdapter::class,
                'directory' => 'data/cache/DoctrineCache',
                'namespace' => 'psr-container-doctrine',
            ],
            'my_cache_provider' => [
                'class' => CustomCacheProvider::class, //The class is looked up in the container
            ],
        ],
        'types' => [],
        'migrations' => [
            'orm_default' => [
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
            ],
        ],
    ],
    'dependencies' => [
        'factories' => [
            Command\CurrentCommand::class => CommandFactory::class,
            Command\DiffCommand::class => CommandFactory::class,
            Command\DumpSchemaCommand::class => CommandFactory::class,
            Command\ExecuteCommand::class => CommandFactory::class,
            Command\GenerateCommand::class => CommandFactory::class,
            Command\LatestCommand::class => CommandFactory::class,
            Command\ListCommand::class => CommandFactory::class,
            Command\MigrateCommand::class => CommandFactory::class,
            Command\RollupCommand::class => CommandFactory::class,
            Command\SyncMetadataCommand::class => CommandFactory::class,
            Command\StatusCommand::class => CommandFactory::class,
            Command\UpToDateCommand::class => CommandFactory::class,
            Command\VersionCommand::class => CommandFactory::class,

            DependencyFactory::class => DependencyFactoryFactory::class,
            ConfigurationLoader::class => ConfigurationLoaderFactory::class,

            FilesystemAdapter::class => static function (ContainerInterface $container): FilesystemAdapter {
                $config = $container->get('config');
                $params = $config['doctrine']['cache']['filesystem'];

                return new FilesystemAdapter(
                    $params['namespace'],
                    $params['directory'],
                );
            },
        ],
    ],
];
