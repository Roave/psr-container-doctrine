<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;
use function array_key_exists;
use function class_exists;
use function is_array;
use function is_subclass_of;

/**
 * @method MappingDriver __invoke(ContainerInterface $container)
 */
final class DriverFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey)
    {
        $config = $this->retrieveConfig($container, $configKey, 'driver');

        if (! array_key_exists('class', $config)) {
            throw new OutOfBoundsException('Missing "class" config key');
        }

        if (! is_array($config['paths'])) {
            $config['paths'] = [$config['paths']];
        }

        if ($config['class'] === AnnotationDriver::class || is_subclass_of($config['class'], AnnotationDriver::class)) {
            $driver = new $config['class'](
                new CachedReader(
                    new AnnotationReader(),
                    $this->retrieveDependency($container, $config['cache'], 'cache', CacheFactory::class)
                ),
                $config['paths']
            );
        }

        if ($config['extension'] !== null
            && ($config['class'] === FileDriver::class || is_subclass_of($config['class'], FileDriver::class))
        ) {
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
     * {@inheritdoc}
     */
    protected function getDefaultConfig(string $configKey) : array
    {
        return [
            'paths' => [],
            'extension' => null,
            'drivers' => [],
        ];
    }
}
