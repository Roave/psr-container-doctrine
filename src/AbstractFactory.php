<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Psr\Container\ContainerInterface;

use function array_key_exists;
use function array_replace_recursive;
use function sprintf;

/**
 * @internal
 *
 * @template T
 */
abstract class AbstractFactory
{
    /** @param non-empty-string $configKey */
    final public function __construct(private string $configKey = 'orm_default')
    {
    }

    /** @return T */
    public function __invoke(ContainerInterface $container): mixed
    {
        return $this->createWithConfig($container, $this->configKey);
    }

    /**
     * Creates a new instance from a specified config.
     *
     * @param non-empty-string $configKey
     *
     * @return T
     */
    abstract protected function createWithConfig(ContainerInterface $container, string $configKey): mixed;

    /**
     * Returns the default config.
     *
     * @return array<non-empty-string, mixed>
     */
    abstract protected function getDefaultConfig(string $configKey): array;

    /**
     * Retrieves the config for a specific section.
     *
     * @return array<non-empty-string, mixed>
     */
    final protected function retrieveConfig(ContainerInterface $container, string $configKey, string $section): array
    {
        $applicationConfig = $container->has('config') ? $container->get('config') : [];
        $sectionConfig     = $applicationConfig['doctrine'][$section] ?? [];

        if (array_key_exists($configKey, $sectionConfig)) {
            return array_replace_recursive($this->getDefaultConfig($configKey), $sectionConfig[$configKey]);
        }

        return $this->getDefaultConfig($configKey);
    }

    /**
     * Retrieves a dependency through the container.
     *
     * If the container does not know about the dependency, it is pulled from a fresh factory. This saves the user from
     * registering factories which they are not going to access themselves at all, and thus minimized configuration.
     *
     * @psalm-param class-string<AbstractFactory> $factoryClassName
     */
    final protected function retrieveDependency(ContainerInterface $container, string $configKey, string $section, string $factoryClassName): mixed
    {
        $containerKey = sprintf('doctrine.%s.%s', $section, $configKey);

        if ($container->has($containerKey)) {
            return $container->get($containerKey);
        }

        return (new $factoryClassName($configKey))->__invoke($container);
    }
}
