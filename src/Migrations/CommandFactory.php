<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Migrations;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\Exception\DomainException;

use function assert;
use function is_a;

/** @extends AbstractFactory<DoctrineCommand> */
final readonly class CommandFactory extends AbstractFactory
{
    /** @psalm-param class-string<DoctrineCommand>|'' $requestedName */
    public function __invoke(ContainerInterface $container, string $requestedName = ''): DoctrineCommand
    {
        if (! is_a($requestedName, DoctrineCommand::class, true)) {
            throw DomainException::forInvalidMigrationsCommand($requestedName);
        }

        return $this->createWithConfig($container, $this->configKey, $requestedName);
    }

    /**
     * @param non-empty-string                 $configKey
     * @param class-string<DoctrineCommand>|'' $requestedName
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey, string $requestedName = ''): DoctrineCommand
    {
        if ($container->has(DependencyFactory::class)) {
            $dependencyFactory = $container->get(DependencyFactory::class);
        } else {
            $dependencyFactory = (new DependencyFactoryFactory($configKey))($container);
        }

        assert($requestedName !== '');

        /** @psalm-suppress UnsafeInstantiation */
        return new $requestedName($dependencyFactory);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [];
    }
}
