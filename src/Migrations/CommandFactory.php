<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Migrations;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\Exception\DomainException;
use function is_a;

class CommandFactory extends AbstractFactory
{
    /** @psalm-var class-string<DoctrineCommand>|'' */
    private $requestedName;

    /**
     * @psalm-param class-string<DoctrineCommand>|'' $requestedName
     */
    public function __invoke(ContainerInterface $container, string $requestedName = '') : DoctrineCommand
    {
        if (! is_a($requestedName, DoctrineCommand::class, true)) {
            throw DomainException::forInvalidMigrationsCommand($requestedName);
        }

        $this->requestedName = $requestedName;

        return parent::__invoke($container);
    }

    protected function createWithConfig(ContainerInterface $container, string $configKey) : DoctrineCommand
    {
        if ($container->has(DependencyFactory::class)) {
            $dependencyFactory = $container->get(DependencyFactory::class);
        } else {
            $dependencyFactory = (new DependencyFactoryFactory($configKey))($container);
        }

        return new $this->requestedName($dependencyFactory);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig(string $configKey) : array
    {
        return [];
    }
}
