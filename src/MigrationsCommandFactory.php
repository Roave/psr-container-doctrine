<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Migrations\Tools\Console\Command\AbstractCommand;
use Psr\Container\ContainerInterface;
use function array_key_exists;
use function class_exists;
use function in_array;
use function sprintf;
use function str_replace;
use function strtolower;
use function ucfirst;

class MigrationsCommandFactory
{
    /** @var string */
    private $commandKey;

    /** @var array<string> */
    private static $availableCommands = [
        'abstract',
        'diff',
        'dumpschema',
        'execute',
        'generate',
        'latest',
        'migrate',
        'rollup',
        'status',
        'uptodate',
        'version',
    ];

    public function __construct(string $commandKey)
    {
        if (! in_array($commandKey, self::$availableCommands)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Command %s is not available',
                $commandKey
            ));
        }

        $this->commandKey = ucfirst(strtolower($commandKey));
    }

    /**
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
     * @return mixed
     *
     * @throws Exception\DomainException
     */
    public function __invoke(ContainerInterface $container)
    {
        $className = str_replace('Abstract', $this->commandKey, AbstractCommand::class);

        $configuration = $container->get('doctrine.migrations');

        if (! class_exists($className)) {
            throw new Exception\DomainException(sprintf(
                'Command class %s does not exist',
                $className
            ));
        }

        $command = new $className();

        $command->setMigrationConfiguration($configuration);

        return $command;
    }
}
