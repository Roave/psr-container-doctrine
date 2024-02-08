<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Cache\NullCache;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;

use function array_key_exists;

/** @extends AbstractFactory<CacheItemPoolInterface> */
final class CacheFactory extends AbstractFactory
{
    protected function createWithConfig(ContainerInterface $container, string $configKey): CacheItemPoolInterface
    {
        $config = $this->retrieveConfig($container, $configKey, 'cache');

        if (! array_key_exists('class', $config)) {
            throw OutOfBoundsException::forMissingConfigKey('doctrine.cache.' . $configKey . '.class');
        }

        $cache = $container->has($config['class']) ? $container->get($config['class']) : new $config['class']();

        if ($cache instanceof CacheItemPoolInterface) {
            return $cache;
        }

        throw InvalidArgumentException::fromUnsupportedCache($cache);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        if ($configKey === NullCache::class) {
            return [
                'class' => NullCache::class,
            ];
        }

        return [];
    }
}
