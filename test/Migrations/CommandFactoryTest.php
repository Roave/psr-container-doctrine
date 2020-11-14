<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Doctrine\Migrations\Tools\Console\Command;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\CommandFactory;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;

class CommandFactoryTest extends TestCase
{
    /**
     * @dataProvider commandClassProvider
     * @psalm-param class-string $commandClass
     */
    public function testFactoryReturnsCommand(string $commandClass) : void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('getSchemaManager')
            ->willReturn($this->createMock(AbstractSchemaManager::class));
        $connection->method('getDatabasePlatform')
            ->willReturn($this->createMock(AbstractPlatform::class));

        $container = $this->createMock(ContainerInterface::class);

        $migrationsConfiguration = new MigrationsConfiguration($connection);

        $container->expects($this->once())
            ->method('get')
            ->willReturnMap(
                [
                    ['doctrine.migrations', $migrationsConfiguration],
                ]
            );

        $factory = new CommandFactory();
        $this->assertInstanceOf($commandClass, $factory($container, $commandClass));
    }

    /**
     * @return array<array<class-string>>
     */
    public function commandClassProvider() : array
    {
        return [
            [Command\DiffCommand::class],
            [Command\DumpSchemaCommand::class],
            [Command\ExecuteCommand::class],
            [Command\GenerateCommand::class],
            [Command\LatestCommand::class],
            [Command\MigrateCommand::class],
            [Command\RollupCommand::class],
            [Command\StatusCommand::class],
            [Command\UpToDateCommand::class],
            [Command\VersionCommand::class],
        ];
    }

    public function testFactoryWithInvalidCommand() : void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('getSchemaManager')
            ->willReturn($this->createMock(AbstractSchemaManager::class));
        $connection->method('getDatabasePlatform')
            ->willReturn($this->createMock(AbstractPlatform::class));

        $container = $this->createMock(ContainerInterface::class);

        $migrationsConfiguration = new MigrationsConfiguration($connection);

        $container->method('get')
            ->willReturnMap(
                [
                    ['doctrine.migrations', $migrationsConfiguration],
                ]
            );

        $factory = new CommandFactory();
        $this->expectException(InvalidArgumentException::class);
        $factory($container, ConsoleRunner::class);
    }
}
