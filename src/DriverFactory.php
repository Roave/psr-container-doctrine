<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;

use function array_key_exists;
use function assert;
use function class_exists;
use function is_array;
use function is_string;
use function is_subclass_of;

/** @extends AbstractFactory<MappingDriver> */
final class DriverFactory extends AbstractFactory
{
    protected function createWithConfig(ContainerInterface $container, string $configKey): MappingDriver
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

        if ($config['extension'] !== null && is_subclass_of($config['class'], FileDriver::class)) {
            /** @psalm-suppress UnsafeInstantiation */
            $driver = new $config['class']($config['paths'], $config['extension']);
        } else {
            $driver = new $config['class']($config['paths']);
        }

        assert($driver instanceof MappingDriver);

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
}
