<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\DomainException;
use Roave\PsrContainerDoctrine\Migrations\CommandFactory;
use stdClass;

final class CommandFactoryTest extends TestCase
{
    /**
     * @psalm-param class-string $commandClass
     *
     * @dataProvider commandClassProvider
     */
    public function testReturnsCommandWhenContainerHasDependencyFactory(string $commandClass): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->willReturnMap(
                [
                    [DependencyFactory::class, true],
                ],
            );
        $container->expects($this->once())
            ->method('get')
            ->willReturnMap(
                [
                    [DependencyFactory::class, $this->createMock(DependencyFactory::class)],
                ],
            );

        $factory = new CommandFactory();
        /** @psalm-suppress ArgumentTypeCoercion */
        self::assertInstanceOf($commandClass, $factory($container, $commandClass));
    }

    /**
     * @psalm-param class-string $commandClass
     *
     * @dataProvider commandClassProvider
     */
    public function testReturnsCommandWhenContainerHasNoDependencyFactory(string $commandClass): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->willReturnMap(
                [
                    [DependencyFactory::class, false],
                    ['doctrine.entity_manager.orm_default', true],
                ],
            );
        $container->method('get')
            ->willReturnMap(
                [
                    [DependencyFactory::class, $this->createMock(DependencyFactory::class)],
                    [ConfigurationLoader::class, $this->createMock(ConfigurationLoader::class)],
                    ['doctrine.entity_manager.orm_default', $this->createMock(EntityManagerInterface::class)],
                ],
            );

        $factory = new CommandFactory();
        /** @psalm-suppress ArgumentTypeCoercion */
        self::assertInstanceOf($commandClass, $factory($container, $commandClass));
    }

    /** @return array<array<class-string>> */
    public function commandClassProvider(): array
    {
        return [
            [Command\CurrentCommand::class],
            [Command\DiffCommand::class],
            [Command\DumpSchemaCommand::class],
            [Command\ExecuteCommand::class],
            [Command\GenerateCommand::class],
            [Command\LatestCommand::class],
            [Command\ListCommand::class],
            [Command\MigrateCommand::class],
            [Command\RollupCommand::class],
            [Command\StatusCommand::class],
            [Command\SyncMetadataCommand::class],
            [Command\UpToDateCommand::class],
            [Command\VersionCommand::class],
        ];
    }

    public function testFactoryThrowsForInvalidCommand(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new CommandFactory();
        $this->expectException(DomainException::class);
        /** @psalm-suppress InvalidArgument */
        $factory($container, stdClass::class);
    }
}
