<?php

declare(strict_types=1);

use App\Doctrine\CustomCacheProvider;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\PredisCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\WinCacheCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\Common\Cache\ZendDataCache;
use Doctrine\DBAL\Driver\PDOMySql\Driver;

return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'result_cache' => 'array',
                'metadata_cache' => 'array',
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
            ],
        ],
        'cache' => [
            'apcu' => [
                'class' => ApcuCache::class,
                'namespace' => 'psr-container-doctrine',
            ],
            'array' => [
                'class' => ArrayCache::class,
                'namespace' => 'psr-container-doctrine',
            ],
            'filesystem' => [
                'class' => FilesystemCache::class,
                'directory' => 'data/cache/DoctrineCache',
                'namespace' => 'psr-container-doctrine',
            ],
            'memcache' => [
                'class' => MemcacheCache::class,
                'instance' => 'my_memcache_alias',
                'namespace' => 'psr-container-doctrine',
            ],
            'memcached' => [
                'class' => MemcachedCache::class,
                'instance' => 'my_memcached_alias',
                'namespace' => 'psr-container-doctrine',
            ],
            'phpfile' => [
                'class' => PhpFileCache::class,
                'directory' => 'data/cache/DoctrineCache',
                'namespace' => 'psr-container-doctrine',
            ],
            'predis' => [
                'class' => PredisCache::class,
                'instance' => 'my_predis_alias',
                'namespace' => 'psr-container-doctrine',
            ],
            'redis' => [
                'class' => RedisCache::class,
                'instance' => 'my_redis_alias',
                'namespace' => 'psr-container-doctrine',
            ],
            'wincache' => [
                'class' => WinCacheCache::class,
                'namespace' => 'psr-container-doctrine',
            ],
            'xcache' => [
                'class' => XcacheCache::class,
                'namespace' => 'psr-container-doctrine',
            ],
            'zenddata' => [
                'class' => ZendDataCache::class,
                'namespace' => 'psr-container-doctrine',
            ],
            'my_cache_provider' => [
                'class' => CustomCacheProvider::class, //The class is looked up in the container
            ],
            'chain' => [
                'class' => ChainCache::class,
                'providers' => ['array', 'redis'], // you can use any provider listed above
                'namespace' => 'psr-container-doctrine', // will be applied to all providers in the chain
            ],
        ],
        'types' => [],
    ],
];
