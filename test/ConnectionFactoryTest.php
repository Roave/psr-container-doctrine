<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\API\MySQL\ExceptionConverter;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver as PDOMySQLDriver;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver as PDOSqliteDriver;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\ORM\Configuration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Roave\PsrContainerDoctrine\ConnectionFactory;

use function defined;
use function in_array;
use function sprintf;

final class ConnectionFactoryTest extends TestCase
{
    private Configuration $configuration;

    public function setUp(): void
    {
        $this->configuration = $this->createMock(Configuration::class);
    }

    public function testDefaultsThroughException(): void
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM is somewhat funky here');
        }

        $factory   = new ConnectionFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturnCallback(
            static function (string $id): bool {
                // Return false for config, true for anything else
                return $id !== 'config';
            },
        );
        $container->method('get')->willReturnCallback(
            function () {
                return $this->configuration;
            },
        );

        // This is actually quite tricky. We cannot really test the pure defaults, as that would require a MySQL
        // connection without a username and password. Since that can't work, we just verify that we get an exception
        // with the right backtrace, and test the other defaults with a pure memory-database later.
        try {
            $factory($container);
        } catch (ConnectionException $e) {
            foreach ($e->getTrace() as $entry) {
                if (
                    isset($entry['class'])
                    && in_array($entry['class'], [
                        PDOMySQLDriver::class,
                        AbstractMySQLDriver::class,
                        ExceptionConverter::class,
                    ], true)
                ) {
                    /** @todo find a better way to add to assertion count... */
                    $this->addToAssertionCount(1);

                    return;
                }
            }

            $this->fail('Exception was not raised by PDOMySql');

            return;
        }

        $this->fail('An expected exception was not raised');
    }

    public function testDefaults(): void
    {
        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer());

        self::assertSame($this->configuration, $connection->getConfiguration());
        self::assertSame([
            'driverClass' => PDOSqliteDriver::class,
            'wrapperClass' => null,
        ], $connection->getParams());
    }

    public function testConfigKeysTakenFromSelf(): void
    {
        $factory    = new ConnectionFactory('orm_other');
        $connection = $factory($this->buildContainer('orm_other', 'orm_other'));

        self::assertSame($this->configuration, $connection->getConfiguration());
    }

    public function testConfigKeysTakenFromConfig(): void
    {
        $factory    = new ConnectionFactory('orm_other');
        $connection = $factory($this->buildContainer('orm_other', 'orm_foo', ['configuration' => 'orm_foo']));

        self::assertSame($this->configuration, $connection->getConfiguration());
    }

    public function testParamsInjection(): void
    {
        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer('orm_default', 'orm_default', [
            'params' => ['username' => 'foo'],
        ]));

        self::assertSame([
            'username' => 'foo',
            'driverClass' => PDOSqliteDriver::class,
            'wrapperClass' => null,
        ], $connection->getParams());
    }

    public function testUrlParam(): void
    {
        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer('orm_default', 'orm_default', [
            'params' => ['url' => 'sqlite3:///:memory:'],
        ]));

        self::assertSame([
            'wrapperClass' => null,
            'driver' => 'sqlite3',
            'host' => 'localhost',
            'memory' => true,
        ], $connection->getParams());
    }

    public function testPrimaryReplicaParams(): void
    {
        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer('orm_default', 'orm_default', [
            'params' => [
                'driver' => 'pdo_mysql',
                'serverVersion' => '8.0.29',
                'primary' => ['url' => '//localhost:4486/foo?charset=utf8mb4'],
                'replica' => [
                    ['url' => '//replica1:3306/foo'],
                    ['url' => '//replica2:3306/foo'],
                ],
            ],
            'wrapper_class' => PrimaryReadReplicaConnection::class,
        ]));

        self::assertSame([
            'driver' => 'pdo_mysql',
            'serverVersion' => '8.0.29',
            'primary' => [
                'host' => 'localhost',
                'port' => 4486,
                'dbname' => 'foo',
                'charset' => 'utf8mb4',
                'driver' => 'pdo_mysql',
            ],
            'replica' => [
                [
                    'host' => 'replica1',
                    'port' => 3306,
                    'dbname' => 'foo',
                    'driver' => 'pdo_mysql',
                ],
                [
                    'host' => 'replica2',
                    'port' => 3306,
                    'dbname' => 'foo',
                    'driver' => 'pdo_mysql',
                ],
            ],
            'wrapperClass' => PrimaryReadReplicaConnection::class,
        ], $connection->getParams());
    }

    public function testDoctrineMappingTypesInjection(): void
    {
        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer(
            'orm_default',
            'orm_default',
            ['doctrine_mapping_types' => ['foo' => 'boolean']],
        ));

        self::assertTrue($connection->getDatabasePlatform()->hasDoctrineTypeMappingFor('foo'));
    }

    public function testCustomTypeDoctrineMappingTypesInjection(): void
    {
        $factory  = new ConnectionFactory();
        $property = (new ReflectionObject($factory))->getProperty('areTypesRegistered');
        $property->setValue($factory, false);

        $connection = $factory($this->buildContainer('orm_default', 'orm_default', [
            'doctrine_mapping_types' => ['foo' => 'custom_type'],
        ]));

        self::assertTrue($connection->getDatabasePlatform()->hasDoctrineTypeMappingFor('foo'));
    }

    /** @param array<string, mixed> $config */
    private function buildContainer(
        string $ownKey = 'orm_default',
        string $configurationKey = 'orm_default',
        array $config = [],
    ): ContainerInterface {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $mockConfig = [
            'doctrine' => [
                'connection' => [
                    $ownKey => $config + ['driver_class' => PDOSqliteDriver::class],
                ],
                'types' => [
                    'custom_type' => BooleanType::class,
                ],
            ],
        ];

        $container->method('get')->willReturnCallback(
            function (string $id) use ($mockConfig, $configurationKey) {
                switch ($id) {
                    case 'config':
                        return $mockConfig;

                    case sprintf('doctrine.configuration.%s', $configurationKey):
                        return $this->configuration;

                    default:
                        $this->fail(sprintf('Unexpected call: Container::get(%s)', $id));
                }
            },
        );

        return $container;
    }
}
