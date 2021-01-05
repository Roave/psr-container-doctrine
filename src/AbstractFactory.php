<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Psr\Container\ContainerInterface;
use function array_key_exists;
use function sprintf;

/**
 * @internal
 * @psalm-consistent-constructor
 */
abstract class AbstractFactory
{
    /** @var string */
    private $configKey;

    /** @internal */
    public function __construct(string $configKey = 'orm_default')
    {
        $this->configKey = $configKey;
    }

    /**
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        return $this->createWithConfig($container, $this->configKey);
    }

    /**
     * Creates a new instance from a specified config, specifically meant to be used as static factory.
     *
     * In case you want to use another config key than "orm_default", you can add the following factory to your config:
     *
     * <code>
     * <?php
     * return [
     *     'doctrine.SECTION.orm_other' => [SpecificFactory::class, 'orm_other'],
     * ];
     * </code>
     *
     * @param mixed[] $arguments
     *
     * @return mixed
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (! array_key_exists(0, $arguments) || ! $arguments[0] instanceof ContainerInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The first argument must be of type %s',
                ContainerInterface::class
            ));
        }

        return (new static($name))->__invoke($arguments[0]);
    }

    /**
     * Creates a new instance from a specified config.
     *
     * @return mixed
     */
    abstract protected function createWithConfig(ContainerInterface $container, string $configKey);

    /**
     * Returns the default config.
     *
     * @return array<string, mixed>
     */
    abstract protected function getDefaultConfig(string $configKey) : array;

    /**
     * Retrieves the config for a specific section.
     *
     * @return array<string, mixed>
     */
    protected function retrieveConfig(ContainerInterface $container, string $configKey, string $section) : array
    {
        $applicationConfig = $container->has('config') ? $container->get('config') : [];
        $sectionConfig     = $applicationConfig['doctrine'][$section] ?? [];

        if (array_key_exists($configKey, $sectionConfig)) {
            return $sectionConfig[$configKey] + $this->getDefaultConfig($configKey);
        }

        return $this->getDefaultConfig($configKey);
    }

    /**
     * Retrieves a dependency through the container.
     *
     * If the container does not know about the dependency, it is pulled from a fresh factory. This saves the user from
     * registering factories which they are not gonna access themself at all, and thus minimized configuration.
     *
     * @return mixed
     *
     * @psalm-param class-string<AbstractFactory> $factoryClassName
     */
    protected function retrieveDependency(ContainerInterface $container, string $configKey, string $section, string $factoryClassName)
    {
        $containerKey = sprintf('doctrine.%s.%s', $section, $configKey);

        if ($container->has($containerKey)) {
            return $container->get($containerKey);
        }

        return (new $factoryClassName($configKey))->__invoke($container);
    }
}
