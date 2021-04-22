<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Migrations\Tools\Console\Command\AbstractCommand;
use Psr\Container\ContainerInterface;

use function class_exists;
use function sprintf;

class MigrationsCommandFactory
{
    /**
     * @throws Exception\DomainException
     */
    public function __invoke(ContainerInterface $container, string $requestedName): AbstractCommand
    {
        $configuration = $container->get('doctrine.migrations');

        if (! class_exists($requestedName)) {
            throw new Exception\DomainException(sprintf(
                'Requested class %s does not exist',
                $requestedName
            ));
        }

        $command = new $requestedName();

        if (! $command instanceof AbstractCommand) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Requested class %s must be of type %s',
                $requestedName,
                AbstractCommand::class
            ));
        }

        $command->setMigrationConfiguration($configuration);

        return $command;
    }
}
