<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\CacheAdapter;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\CompatibilityAnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;

use function array_key_exists;
use function class_exists;
use function is_array;
use function is_string;
use function is_subclass_of;
use function method_exists;

/** @method MappingDriver __invoke(ContainerInterface $container) */
final class DriverFactory extends AbstractFactory
{
    private static bool $isAnnotationLoaderRegistered = false;

    /**
     * {@inheritDoc}
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey)
    {
        $config = $this->retrieveConfig($container, $configKey, 'driver');

        if (! array_key_exists('class', $config)) {
            throw OutOfBoundsException::forMissingConfigKey('doctrine.driver.' . $configKey . '.class');
        }

        if (! is_string($config['class']) || ! class_exists($config['class'])) {
            throw new InvalidArgumentException('Configured class "' . $config['class'] . '" on key "doctrine.driver.' . $configKey . '.class" does not exists');
        }

        if (! is_array($config['paths'])) {
            $config['paths'] = [$config['paths']];
        }

        if (
            $config['class'] !== AttributeDriver::class
            && ! is_subclass_of($config['class'], AttributeDriver::class)
            && is_subclass_of($config['class'], CompatibilityAnnotationDriver::class)
        ) {
            $this->registerAnnotationLoader();

            /** @psalm-suppress UnsafeInstantiation */
            $driver = new $config['class'](
                $this->createCachedReader($container, $config, new AnnotationReader()),
                $config['paths']
            );
        } elseif ($config['extension'] !== null && is_subclass_of($config['class'], FileDriver::class)) {
            /** @psalm-suppress UnsafeInstantiation */
            $driver = new $config['class']($config['paths'], $config['extension']);
        }

        if (! isset($driver)) {
            $driver = new $config['class']($config['paths']);
        }

        if (array_key_exists('global_basename', $config) && $driver instanceof FileDriver) {
            $driver->setGlobalBasename($config['global_basename']);
        }

        if ($driver instanceof MappingDriverChain) {
            if ($config['default_driver'] !== null) {
                $driver->setDefaultDriver($this->createWithConfig($container, $config['default_driver']));
            }

            foreach ($config['drivers'] as $namespace => $driverName) {
                if ($driverName === null) {
                    continue;
                }

                $driver->addDriver($this->createWithConfig($container, $driverName), $namespace);
            }
        }

        return $driver;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [
            'paths' => [],
            'extension' => null,
            'drivers' => [],
            'default_driver' => null,
        ];
    }

    /**
     * Registers the annotation loader
     */
    private function registerAnnotationLoader(): void
    {
        if (self::$isAnnotationLoaderRegistered) {
            return;
        }

        if (method_exists(AnnotationRegistry::class, 'registerLoader')) {
            AnnotationRegistry::registerLoader('class_exists');
        }

        self::$isAnnotationLoaderRegistered = true;
    }

    /** @param array<string, mixed> $config */
    private function createCachedReader(ContainerInterface $container, array $config, Reader $reader): PsrCachedReader
    {
        $cache = $this->retrieveDependency(
            $container,
            $config['cache'],
            'cache',
            CacheFactory::class,
        );

        if ($cache instanceof Cache) {
            $cache = CacheAdapter::wrap($cache);
        }

        if ($cache instanceof CacheItemPoolInterface) {
            return new PsrCachedReader($reader, $cache);
        }

        throw InvalidArgumentException::fromUnsupportedCache(
            $cache,
        );
    }
}
