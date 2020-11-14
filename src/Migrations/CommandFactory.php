<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Migrations;

use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;

class CommandFactory extends AbstractFactory
{
    /**
     * @psalm-var class-string<DoctrineCommand>
     */
    private $requestedName;

    /**
     * @psalm-param class-string<DoctrineCommand> $requestedName
     */
    public function __invoke(ContainerInterface $container, ?string $requestedName = null) : DoctrineCommand
    {
        $this->requestedName = $requestedName;

        // Let the parent trigger createWithConfig with $configKey
        return parent::__invoke($container);
    }

    protected function createWithConfig(ContainerInterface $container, string $configKey) : DoctrineCommand
    {
        if ($container->has(DependencyFactory::class)) {
            $dependencyFactory = $container->get(DependencyFactory::class);
        } else {
            $dependencyFactory = (new DependencyFactoryFactory($configKey))($container);
        }

        if ($container->has(ConfigurationLoader::class)) {
            $configurationLoader = $container->get(ConfigurationLoader::class);
        } else {
            $configurationLoader = (new DependencyFactoryFactory($configKey))($container);
        }

        return new $this->requestedName($dependencyFactory, $configurationLoader);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig(string $configKey) : array
    {
        return [];
    }
}
