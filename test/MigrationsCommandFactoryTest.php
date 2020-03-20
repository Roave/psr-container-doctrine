<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Doctrine\Migrations\Tools\Console\Command;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;
use Roave\PsrContainerDoctrine\MigrationsCommandFactory;

class MigrationsCommandFactoryTest extends TestCase
{
    public function testStaticCall() : void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('getSchemaManager')
            ->willReturn($this->createMock(AbstractSchemaManager::class));
        $connection->method('getDatabasePlatform')
            ->willReturn($this->createMock(AbstractPlatform::class));

        $container = $this->createMock(ContainerInterface::class);

        $migrationsConfiguration = new MigrationsConfiguration($connection);

        $container->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['doctrine.migrations', $migrationsConfiguration],
                    ]
                )
            );

        $this->assertInstanceOf(Command\ExecuteCommand::class, MigrationsCommandFactory::execute($container));
        $this->assertInstanceOf(Command\DiffCommand::class, MigrationsCommandFactory::diff($container));
    }

    public function testStaticCallWithoutContainer() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The first argument must be of type Psr\Container\ContainerInterface');
        MigrationsCommandFactory::execute();
    }

    public function testStaticCallWithInvalidCommand() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command foo is not available');
        MigrationsCommandFactory::foo($container);
    }
}
