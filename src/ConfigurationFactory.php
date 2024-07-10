<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Configuration;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Cache\NullCache;

use function array_key_exists;
use function assert;
use function is_string;

/** @extends AbstractFactory<Configuration> */
final class ConfigurationFactory extends AbstractFactory
{
    protected function createWithConfig(ContainerInterface $container, string $configKey): Configuration
    {
        $config = $this->retrieveConfig($container, $configKey, 'configuration');

        $configuration = new Configuration();
        $configuration->setProxyDir($config['proxy_dir']);
        $configuration->setProxyNamespace($config['proxy_namespace']);
        $configuration->setAutoGenerateProxyClasses($config['auto_generate_proxy_classes']);
        $configuration->setEntityNamespaces($config['entity_namespaces']);
        $configuration->setCustomDatetimeFunctions($config['datetime_functions']);
        $configuration->setCustomStringFunctions($config['string_functions']);
        $configuration->setCustomNumericFunctions($config['numeric_functions']);
        $configuration->setCustomHydrationModes($config['custom_hydration_modes']);
        if ($config['class_metadata_factory_name'] !== null) {
            $configuration->setClassMetadataFactoryName($config['class_metadata_factory_name']);
        }

        foreach ($config['filters'] as $name => $className) {
            $configuration->addFilter($name, $className);
        }

        $metadataCache = $this->retrieveDependency(
            $container,
            $config['metadata_cache'],
            'cache',
            CacheFactory::class,
        );

        $configuration->setMetadataCache($metadataCache);

        $queryCache = $this->retrieveDependency(
            $container,
            $config['query_cache'],
            'cache',
            CacheFactory::class,
        );

        $configuration->setQueryCache($queryCache);

        $resultCache = $this->retrieveDependency(
            $container,
            $config['result_cache'],
            'cache',
            CacheFactory::class,
        );

        $configuration->setResultCache($resultCache);

        $hydrationCache = $this->retrieveDependency(
            $container,
            $config['hydration_cache'],
            'cache',
            CacheFactory::class,
        );

        $configuration->setHydrationCache($hydrationCache);

        $configuration->setMetadataDriverImpl($this->retrieveDependency(
            $container,
            $config['driver'],
            'driver',
            DriverFactory::class,
        ));

        if (is_string($config['naming_strategy'])) {
            $configuration->setNamingStrategy($container->get($config['naming_strategy']));
        } elseif ($config['naming_strategy'] !== null) {
            $configuration->setNamingStrategy($config['naming_strategy']);
        }

        if (is_string($config['typed_field_mapper'])) {
            $configuration->setTypedFieldMapper($container->get($config['typed_field_mapper']));
        } elseif ($config['typed_field_mapper'] !== null) {
            $configuration->setTypedFieldMapper($config['typed_field_mapper']);
        }

        if (is_string($config['quote_strategy'])) {
            $configuration->setQuoteStrategy($container->get($config['quote_strategy']));
        } elseif ($config['quote_strategy'] !== null) {
            $configuration->setQuoteStrategy($config['quote_strategy']);
        }

        if (is_string($config['repository_factory'])) {
            $configuration->setRepositoryFactory($container->get($config['repository_factory']));
        } elseif ($config['repository_factory'] !== null) {
            $configuration->setRepositoryFactory($config['repository_factory']);
        }

        if (is_string($config['entity_listener_resolver'])) {
            $configuration->setEntityListenerResolver($container->get($config['entity_listener_resolver']));
        } elseif ($config['entity_listener_resolver'] !== null) {
            $configuration->setEntityListenerResolver($config['entity_listener_resolver']);
        }

        if (is_string($config['schema_assets_filter'])) {
            $configuration->setSchemaAssetsFilter($container->get($config['schema_assets_filter']));
        }

        if ($config['default_repository_class_name'] !== null) {
            $configuration->setDefaultRepositoryClassName($config['default_repository_class_name']);
        }

        $resultCache = $configuration->getResultCache();
        if ($config['second_level_cache']['enabled'] && $resultCache) {
            $regionsConfig = new RegionsConfiguration(
                $config['second_level_cache']['default_lifetime'],
                $config['second_level_cache']['default_lock_lifetime'],
            );

            foreach ($config['second_level_cache']['regions'] as $regionName => $regionConfig) {
                if (array_key_exists('lifetime', $regionConfig)) {
                    $regionsConfig->setLifetime($regionName, $regionConfig['lifetime']);
                }

                if (! array_key_exists('lock_lifetime', $regionConfig)) {
                    continue;
                }

                $regionsConfig->setLockLifetime($regionName, $regionConfig['lock_lifetime']);
            }

            $cacheFactory = new DefaultCacheFactory($regionsConfig, $resultCache);
            $cacheFactory->setFileLockRegionDirectory($config['second_level_cache']['file_lock_region_directory']);

            $cacheConfiguration = new CacheConfiguration();
            $cacheConfiguration->setCacheFactory($cacheFactory);
            $cacheConfiguration->setRegionsConfiguration($regionsConfig);

            $configuration->setSecondLevelCacheEnabled(true);
            $configuration->setSecondLevelCacheConfiguration($cacheConfiguration);
        }

        if ($config['middlewares'] !== []) {
            $middlewares = [];
            foreach ($config['middlewares'] as $middleware) {
                assert(is_string($middleware), '`middlewares` must contain a list of container id strings');
                $middlewares[] = $container->get($middleware);
            }

            $configuration->setMiddlewares($middlewares);
        }

        return $configuration;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [
            'metadata_cache' => NullCache::class,
            'query_cache' => NullCache::class,
            'result_cache' => NullCache::class,
            'hydration_cache' => NullCache::class,
            'driver' => $configKey,
            'auto_generate_proxy_classes' => true,
            'proxy_dir' => 'data/cache/DoctrineEntityProxy',
            'proxy_namespace' => 'DoctrineEntityProxy',
            'entity_namespaces' => [],
            'datetime_functions' => [],
            'string_functions' => [],
            'numeric_functions' => [],
            'filters' => [],
            'custom_hydration_modes' => [],
            'naming_strategy' => null,
            'typed_field_mapper' => null,
            'quote_strategy' => null,
            'default_repository_class_name' => null,
            'repository_factory' => null,
            'class_metadata_factory_name' => null,
            'entity_listener_resolver' => null,
            'schema_assets_filter' => null,
            'second_level_cache' => [
                'enabled' => false,
                'default_lifetime' => 3600,
                'default_lock_lifetime' => 60,
                'file_lock_region_directory' => '',
                'regions' => [],
            ],
            'sql_logger' => null,
            'middlewares' => [],
        ];
    }
}
