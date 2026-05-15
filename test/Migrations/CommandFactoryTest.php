<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\DomainException;
use Roave\PsrContainerDoctrine\Migrations\CommandFactory;
use stdClass;

final class CommandFactoryTest extends TestCase
{
    /** @psalm-param class-string $commandClass */
    #[DataProvider('commandClassProvider')]
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
                    [DependencyFactory::class, $this->createStub(DependencyFactory::class)],
                ],
            );

        $factory = new CommandFactory();
        /** @psalm-suppress ArgumentTypeCoercion */
        self::assertInstanceOf($commandClass, $factory($container, $commandClass));
    }

    /** @psalm-param class-string $commandClass */
    #[DataProvider('commandClassProvider')]
    public function testReturnsCommandWhenContainerHasNoDependencyFactory(string $commandClass): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $config    = [
            'doctrine' => [
                'configuration' => [
                    'orm_default' => [
                        'metadata_cache' => 'metadata',
                        'result_cache' => 'result',
                        'query_cache' => 'query',
                        'hydration_cache' => 'hydration',
                        'second_level_cache' => ['enabled' => true],
                    ],
                ],
            ],
        ];

        $container->method('has')
            ->willReturnMap(
                [
                    ['config', true],
                    [DependencyFactory::class, false],
                    [ConfigurationLoader::class, true],
                    ['doctrine.entity_manager.orm_default', true],
                ],
            );
        $container->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    [DependencyFactory::class, $this->createStub(DependencyFactory::class)],
                    [ConfigurationLoader::class, $this->createStub(ConfigurationLoader::class)],
                    ['doctrine.entity_manager.orm_default', $this->createStub(EntityManagerInterface::class)],
                ],
            );

        $factory = new CommandFactory();
        /** @psalm-suppress ArgumentTypeCoercion */
        self::assertInstanceOf($commandClass, $factory($container, $commandClass));
    }

    /** @return array<array<class-string>> */
    public static function commandClassProvider(): array
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
        $container = $this->createStub(ContainerInterface::class);
        $factory   = new CommandFactory();
        $this->expectException(DomainException::class);
        /** @psalm-suppress InvalidArgument */
        $factory($container, stdClass::class);
    }
}
