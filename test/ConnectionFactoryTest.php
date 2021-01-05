<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver as PDOMySQLDriver;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver as PDOSqliteDriver;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Roave\PsrContainerDoctrine\ConnectionFactory;
use function defined;
use function sprintf;

final class ConnectionFactoryTest extends TestCase
{
    /** @var Configuration */
    private $configuration;

    /** @var EventManager */
    private $eventManger;

    /** @var AbstractPlatform */
    private $customPlatform;

    public function setUp() : void
    {
        $this->configuration  = $this->createMock(Configuration::class);
        $this->eventManger    = $this->createMock(EventManager::class);
        $this->customPlatform = $this->createMock(AbstractPlatform::class);
    }

    public function testDefaultsThroughException() : void
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM is somewhat funky here');
        }

        $factory   = new ConnectionFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturnCallback(
            static function (string $id) : bool {
                // Return false for config, true for anything else
                return $id !== 'config';
            }
        );
        $container->method('get')->willReturnCallback(
            function (string $id) {
                if ($id === 'doctrine.configuration.orm_default') {
                    return $this->configuration;
                }

                return $this->eventManger;
            }
        );

        // This is actually quite tricky. We cannot really test the pure defaults, as that would require a MySQL
        // connection without a username and password. Since that can't work, we just verify that we get an exception
        // with the right backtrace, and test the other defaults with a pure memory-database later.
        try {
            $connection = $factory($container);
            $connection->ping();
        } catch (ConnectionException $e) {
            foreach ($e->getTrace() as $entry) {
                if ($entry['class'] === PDOMySQLDriver::class) {
                    /** @psalm-suppress InternalMethod @todo find a better way to add to assertion count... */
                    $this->addToAssertionCount(1);

                    return;
                }
            }

            $this->fail('Exception was not raised by PDOMySql');

            return;
        }

        $this->fail('An expected exception was not raised');
    }

    public function testDefaults() : void
    {
        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer());

        $this->assertSame($this->configuration, $connection->getConfiguration());
        $this->assertSame($this->eventManger, $connection->getEventManager());
        $this->assertSame([
            'driverClass' => PDOSqliteDriver::class,
            'wrapperClass' => null,
            'pdo' => null,
        ], $connection->getParams());
    }

    public function testConfigKeysTakenFromSelf() : void
    {
        $factory    = new ConnectionFactory('orm_other');
        $connection = $factory($this->buildContainer('orm_other', 'orm_other', 'orm_other'));

        $this->assertSame($this->configuration, $connection->getConfiguration());
        $this->assertSame($this->eventManger, $connection->getEventManager());
    }

    public function testConfigKeysTakenFromConfig() : void
    {
        $factory    = new ConnectionFactory('orm_other');
        $connection = $factory($this->buildContainer('orm_other', 'orm_foo', 'orm_bar', [
            'configuration' => 'orm_foo',
            'event_manager' => 'orm_bar',
        ]));

        $this->assertSame($this->configuration, $connection->getConfiguration());
        $this->assertSame($this->eventManger, $connection->getEventManager());
    }

    public function testParamsInjection() : void
    {
        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer('orm_default', 'orm_default', 'orm_default', [
            'params' => ['username' => 'foo'],
        ]));

        $this->assertSame([
            'username' => 'foo',
            'driverClass' => PDOSqliteDriver::class,
            'wrapperClass' => null,
            'pdo' => null,
        ], $connection->getParams());
    }

    public function testDoctrineMappingTypesInjection() : void
    {
        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer(
            'orm_default',
            'orm_default',
            'orm_default',
            ['doctrine_mapping_types' => ['foo' => 'boolean']]
        ));

        $this->assertTrue($connection->getDatabasePlatform()->hasDoctrineTypeMappingFor('foo'));
    }

    public function testDoctrineCommentedTypesInjection() : void
    {
        $type = Type::getType('boolean');

        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer('orm_default', 'orm_default', 'orm_default', [
            'doctrine_commented_types' => [$type],
        ]));

        $this->assertTrue($connection->getDatabasePlatform()->isCommentedDoctrineType($type));
    }

    public function testCustomTypeDoctrineMappingTypesInjection() : void
    {
        $factory  = new ConnectionFactory();
        $property = (new ReflectionObject($factory))->getProperty('areTypesRegistered');
        $property->setAccessible(true);
        $property->setValue($factory, false);

        $connection = $factory($this->buildContainer('orm_default', 'orm_default', 'orm_default', [
            'doctrine_mapping_types' => ['foo' => 'custom_type'],
        ]));

        $this->assertTrue($connection->getDatabasePlatform()->hasDoctrineTypeMappingFor('foo'));
    }

    public function testCustomPlatform() : void
    {
        $factory = new ConnectionFactory();

        $config = [
            'params' => ['platform' => 'custom.platform'],
        ];

        $container = $this->buildContainer('orm_default', 'orm_default', 'orm_default', $config);

        $connection = $factory($container);

        $this->assertSame($this->customPlatform, $connection->getDatabasePlatform());
    }

    /**
     * @param array<string, mixed> $config
     */
    private function buildContainer(
        string $ownKey = 'orm_default',
        string $configurationKey = 'orm_default',
        string $eventManagerKey = 'orm_default',
        array $config = []
    ) : ContainerInterface {
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
            function (string $id) use ($mockConfig, $configurationKey, $eventManagerKey) {
                switch ($id) {
                    case 'config':
                        return $mockConfig;
                    case sprintf('doctrine.configuration.%s', $configurationKey):
                        return $this->configuration;
                    case sprintf('doctrine.event_manager.%s', $eventManagerKey):
                        return $this->eventManger;
                    case 'custom.platform':
                        return $this->customPlatform;
                    default:
                        $this->fail(sprintf('Unexpected call: Container::get(%s)', $id));
                }
            }
        );

        return $container;
    }
}
