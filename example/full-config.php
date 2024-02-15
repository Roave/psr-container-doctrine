<?php

declare(strict_types=1);

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Driver as DbalDriver;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\SQLite3\Driver;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\EntityManagerFactory;
use Roave\PsrContainerDoctrine\Migrations\CommandFactory;
use Roave\PsrContainerDoctrine\Migrations\ConfigurationLoaderFactory;
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
                'generate_proxies' => true,
                'proxy_dir' => 'data/cache/DoctrineEntityProxy',
                'proxy_namespace' => 'DoctrineEntityProxy',
                'entity_namespaces' => [],
                'datetime_functions' => [],
                'string_functions' => [],
                'numeric_functions' => [],
                'filters' => [],
                'custom_hydration_modes' => [],
                'naming_strategy' => null,
                'quote_strategy' => null,
                'default_repository_class_name' => EntityRepository::class,
                'repository_factory' => null,
                'class_metadata_factory_name' => ClassMetadataFactory::class,
                'entity_listener_resolver' => null,
                'second_level_cache' => [
                    'enabled' => false,
                    'default_lifetime' => 3600,
                    'default_lock_lifetime' => 60,
                    'file_lock_region_directory' => '',
                    'regions' => [],
                ],
                // List of middlewares doctrine will use to decorate the `Driver` component.
                // (see https://github.com/doctrine/dbal/blob/3.3.2/docs/en/reference/architecture.rst#middlewares)
                'middlewares' => ['app.foo.middleware'],
                'schema_assets_filter' => 'my_schema_assets_filter',
            ],
        ],
        'connection' => [
            'orm_default' => [
                'driver_class' => Driver::class,
                'params' => ['serverVersion' => 'mariadb-11.0'],
                'doctrine_type_mappings' => [],
            ],
            'orm_default_with_url' => [
                'configuration' => 'orm_default',
                'driver_class' => Driver::class,
                'wrapper_class' => PrimaryReadReplicaConnection::class,
                'params' => [
                    'primary' => ['url' => '//app:secr3t@primary.cluster:3306/foo?charset=utf8mb4'],
                    'replica' => [
                        ['url' => '//replica1.cluster:3306/foo?charset=utf8mb4'],
                        ['url' => '//replica2.cluster:3306/foo?charset=utf8mb4'],
                    ],
                ],
            ],
            'orm_default_with_array' => [
                'configuration' => 'orm_default',
                'driver_class' => Driver::class,
                'wrapper_class' => PrimaryReadReplicaConnection::class,
                'params' => [
                    'primary' => [
                        'user' => 'app',
                        'password' => 'secr3t',
                        'host' => 'primary.cluster',
                        'port' => 3306,
                        'dbname' => 'foo',
                        'charset' => 'utf8mb4',
                    ],
                    'replica' => [
                        [
                            'host' => 'replica1.cluster',
                            'port' => 3306,
                            'dbname' => 'foo',
                            'charset' => 'utf8mb4',
                        ],
                        [
                            'host' => 'replica2.cluster',
                            'port' => 3306,
                            'dbname' => 'foo',
                            'charset' => 'utf8mb4',
                        ],
                    ],
                ],
            ],
        ],
        'entity_manager' => [
            'orm_default' => [
                'connection' => 'orm_default', // Actually defaults to the entity manager config key, not hard-coded
                'configuration' => 'orm_default', // Actually defaults to the entity manager config key, not hard-coded
                'event_manager' => 'orm_default', // Actually defaults to the connection config key, not hard-coded
            ],
            'orm_default_with_url' => [
                'connection' => 'orm_default_with_url',
                'configuration' => 'orm_default', // Actually defaults to the entity manager config key, not hard-coded
                'event_manager' => 'orm_default', // Actually defaults to the connection config key, not hard-coded
            ],
            'orm_default_with_array' => [
                'connection' => 'orm_default_with_array',
                'configuration' => 'orm_default', // Actually defaults to the entity manager config key, not hard-coded
                'event_manager' => 'orm_default', // Actually defaults to the connection config key, not hard-coded
            ],
        ],
        'event_manager' => [
            'orm_default' => [
                'subscribers' => [],
            ],
        ],
        'driver' => [
            'orm_default' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/Entity/', // If this config is is src/App/ConfigProvider.php
                ],
                'extension' => null,
                'drivers' => [],
                'cache' => 'array',
            ],
        ],
        'cache' => [
            'array' => [
                'class' => ArrayAdapter::class,
            ],
            'filesystem' => [
                'class' => FilesystemAdapter::class,
            ],
            // 'my_cache_provider' => [
            //     'class' => CustomCacheProvider::class, //The class is looked up in the container
            // ],
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

            EntityManagerInterface::class => EntityManagerFactory::class,
            DependencyFactory::class => DependencyFactoryFactory::class,
            ConfigurationLoader::class => ConfigurationLoaderFactory::class,

            'doctrine.entity_manager.orm_default_with_url' => static function (ContainerInterface $container): EntityManagerInterface {
                return (new EntityManagerFactory('orm_default_with_url'))->__invoke($container);
            },
            'doctrine.entity_manager.orm_default_with_array' => static function (ContainerInterface $container): EntityManagerInterface {
                return (new EntityManagerFactory('orm_default_with_url'))->__invoke($container);
            },

            'my_schema_assets_filter' => static function (): callable {
                /**
                 * Filter out assets (table, sequence) by name from Schema
                 * because ones have no mapping and this cause unwanted create|drop statements in migration
                 * generated with migrations:diff command when compare ORM schema and schema introspected from database
                 */
                return static fn (AbstractAsset|string $asset): bool => ! in_array(
                    $asset instanceof AbstractAsset ? $asset->getName() : $asset,
                    [
                        'sequence_to_generate_value',
                        'table_without_doctrine_mapping',
                    ],
                    true,
                );
            },

            'app.foo.middleware' => static function (): Middleware {
                return new class implements Middleware {
                    public function wrap(DbalDriver $driver): DbalDriver
                    {
                        return $driver;
                    }
                };
            },

            FilesystemAdapter::class => static function (): FilesystemAdapter {
                return new FilesystemAdapter(
                    'psr-container-doctrine',
                    3600,
                    __DIR__ . '/data/cache/DoctrineCache',
                );
            },
        ],
    ],
];
