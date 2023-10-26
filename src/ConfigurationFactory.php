<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\CacheAdapter;
use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Configuration;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function assert;
use function is_string;

/** @method Configuration __invoke(ContainerInterface $container) */
final class ConfigurationFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey)
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
        $configuration->setClassMetadataFactoryName($config['class_metadata_factory_name']);

        foreach ($config['named_queries'] as $name => $dql) {
            $configuration->addNamedQuery($name, $dql);
        }

        foreach ($config['named_native_queries'] as $name => $query) {
            $configuration->addNamedNativeQuery($name, $query['sql'], $query['rsm']);
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

        $this->processCacheImplementation(
            $configuration,
            $metadataCache,
            [$configuration, 'setMetadataCache'],
        );

        $queryCache = $this->retrieveDependency(
            $container,
            $config['query_cache'],
            'cache',
            CacheFactory::class,
        );

        $this->processCacheImplementation(
            $configuration,
            $queryCache,
            [$configuration, 'setQueryCache'],
        );

        $resultCache = $this->retrieveDependency(
            $container,
            $config['result_cache'],
            'cache',
            CacheFactory::class,
        );

        $this->processCacheImplementation(
            $configuration,
            $resultCache,
            [$configuration, 'setResultCache'],
        );

        $hydrationCache = $this->retrieveDependency(
            $container,
            $config['hydration_cache'],
            'cache',
            CacheFactory::class,
        );

        $this->processCacheImplementation(
            $configuration,
            $hydrationCache,
            [$configuration, 'setHydrationCache'],
        );

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

        if ($config['default_repository_class_name'] !== null) {
            $configuration->setDefaultRepositoryClassName($config['default_repository_class_name']);
        }

        if ($config['second_level_cache']['enabled']) {
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

            /** @psalm-suppress PossiblyInvalidArgument */
            $cacheFactory = new DefaultCacheFactory($regionsConfig, $resultCache);
            $cacheFactory->setFileLockRegionDirectory($config['second_level_cache']['file_lock_region_directory']);

            $cacheConfiguration = new CacheConfiguration();
            $cacheConfiguration->setCacheFactory($cacheFactory);
            $cacheConfiguration->setRegionsConfiguration($regionsConfig);

            $configuration->setSecondLevelCacheEnabled(true);
            $configuration->setSecondLevelCacheConfiguration($cacheConfiguration);
        }

        if (is_string($config['sql_logger'])) {
            $configuration->setSQLLogger($container->get($config['sql_logger']));
        } elseif ($config['sql_logger'] !== null) {
            $configuration->setSQLLogger($config['sql_logger']);
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
            'metadata_cache' => 'array',
            'query_cache' => 'array',
            'result_cache' => 'array',
            'hydration_cache' => 'array',
            'driver' => $configKey,
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
            'middlewares' => [],
        ];
    }

    /** @param callable(CacheItemPoolInterface):void $setCacheOnConfiguration */
    private function processCacheImplementation(
        Configuration $configuration,
        CacheItemPoolInterface|Cache $cache,
        callable $setCacheOnConfiguration,
    ): void {
        if ($cache instanceof Cache) {
            $cache = CacheAdapter::wrap($cache);
        }

        $setCacheOnConfiguration($cache);
    }
}
