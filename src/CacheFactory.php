<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\PredisCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\WinCacheCache;
use Doctrine\Common\Cache\ZendDataCache;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;

use function array_key_exists;
use function array_map;
use function assert;
use function is_array;
use function is_string;

/**
 * @method Cache __invoke(ContainerInterface $container)
 */
final class CacheFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey)
    {
        $config = $this->retrieveConfig($container, $configKey, 'cache');

        if (! array_key_exists('class', $config)) {
            throw OutOfBoundsException::forMissingConfigKey('class');
        }

        $instance = null;

        if (array_key_exists('instance', $config)) {
            $instance = is_string($config['instance']) ? $container->get($config['instance']) : $config['instance'];
        }

        switch ($config['class']) {
            case FilesystemCache::class:
            case PhpFileCache::class:
                $cache = new $config['class']($config['directory']);
                break;

            case PredisCache::class:
                assert($instance !== null);
                $cache = new PredisCache($instance);
                break;

            case ChainCache::class:
                $providers = array_map(
                    function ($provider) use ($container): CacheProvider {
                        return $this->createWithConfig($container, $provider);
                    },
                    is_array($config['providers']) ? $config['providers'] : []
                );
                $cache     = new ChainCache($providers);
                break;

            default:
                $cache = $container->has($config['class']) ? $container->get($config['class']) : new $config['class']();
        }

        if ($cache instanceof MemcachedCache) {
            assert($instance !== null);
            $cache->setMemcached($instance);
        } elseif ($cache instanceof RedisCache) {
            assert($instance !== null);
            $cache->setRedis($instance);
        }

        if ($cache instanceof CacheProvider && array_key_exists('namespace', $config)) {
            $cache->setNamespace($config['namespace']);
        }

        return $cache;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        switch ($configKey) {
            case 'apcu':
                return [
                    'class' => ApcuCache::class,
                    'namespace' => 'psr-container-doctrine',
                ];

            case 'array':
                return [
                    'class' => ArrayCache::class,
                    'namespace' => 'psr-container-doctrine',
                ];

            case 'filesystem':
                return [
                    'class' => FilesystemCache::class,
                    'directory' => 'data/cache/DoctrineCache',
                    'namespace' => 'psr-container-doctrine',
                ];

            case 'memcached':
                return [
                    'class' => MemcachedCache::class,
                    'instance' => 'my_memcached_alias',
                    'namespace' => 'psr-container-doctrine',
                ];

            case 'phpfile':
                return [
                    'class' => PhpFileCache::class,
                    'directory' => 'data/cache/DoctrineCache',
                    'namespace' => 'psr-container-doctrine',
                ];

            case 'predis':
                return [
                    'class' => PredisCache::class,
                    'instance' => 'my_predis_alias',
                    'namespace' => 'psr-container-doctrine',
                ];

            case 'redis':
                return [
                    'class' => RedisCache::class,
                    'instance' => 'my_redis_alias',
                    'namespace' => 'psr-container-doctrine',
                ];

            case 'wincache':
                return [
                    'class' => WinCacheCache::class,
                    'namespace' => 'psr-container-doctrine',
                ];

            case 'zenddata':
                return [
                    'class' => ZendDataCache::class,
                    'namespace' => 'psr-container-doctrine',
                ];

            case 'chain':
                return [
                    'class' => ChainCache::class,
                    'namespace' => 'psr-container-doctrine',
                    'providers' => [],
                ];

            default:
                return [];
        }
    }
}
