<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as PDOSqliteDriver;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Roave\PsrContainerDoctrine\ConnectionFactory;
use function defined;
use function sprintf;

class ConnectionFactoryTest extends TestCase
{
    /** @var Configuration */
    private $configuration;

    /** @var EventManager */
    private $eventManger;

    public function setUp() : void
    {
        $this->configuration = $this->prophesize(Configuration::class)->reveal();
        $this->eventManger   = $this->prophesize(EventManager::class)->reveal();
    }

    public function testDefaultsThroughException() : void
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM is somewhat funky here');
        }

        $factory   = new ConnectionFactory();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(false);
        $container->has('doctrine.configuration.orm_default')->willReturn(true);
        $container->get('doctrine.configuration.orm_default')->willReturn($this->configuration);
        $container->has('doctrine.event_manager.orm_default')->willReturn(true);
        $container->get('doctrine.event_manager.orm_default')->willReturn($this->eventManger);

        // This is actually quite tricky. We cannot really test the pure defaults, as that would require a MySQL
        // connection without a username and password. Since that can't work, we just verify that we get an exception
        // with the right backtrace, and test the other defaults with a pure memory-database later.
        try {
            $factory($container->reveal());
        } catch (ConnectionException $e) {
            foreach ($e->getTrace() as $entry) {
                if ($entry['class'] === 'Doctrine\DBAL\Driver\PDOMySql\Driver') {
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
        $connection = $factory($this->buildContainer()->reveal());

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
        $connection = $factory($this->buildContainer('orm_other', 'orm_other', 'orm_other')->reveal());

        $this->assertSame($this->configuration, $connection->getConfiguration());
        $this->assertSame($this->eventManger, $connection->getEventManager());
    }

    public function testConfigKeysTakenFromConfig() : void
    {
        $factory    = new ConnectionFactory('orm_other');
        $connection = $factory($this->buildContainer('orm_other', 'orm_foo', 'orm_bar', [
            'configuration' => 'orm_foo',
            'event_manager' => 'orm_bar',
        ])->reveal());

        $this->assertSame($this->configuration, $connection->getConfiguration());
        $this->assertSame($this->eventManger, $connection->getEventManager());
    }

    public function testParamsInjection() : void
    {
        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer('orm_default', 'orm_default', 'orm_default', [
            'params' => ['username' => 'foo'],
        ])->reveal());

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
        $connection = $factory($this->buildContainer('orm_default', 'orm_default', 'orm_default', [
            'doctrine_mapping_types' => ['foo' => 'boolean'],
        ])->reveal());

        $this->assertTrue($connection->getDatabasePlatform()->hasDoctrineTypeMappingFor('foo'));
    }

    public function testDoctrineCommentedTypesInjection() : void
    {
        $type = Type::getType('boolean');

        $factory    = new ConnectionFactory();
        $connection = $factory($this->buildContainer('orm_default', 'orm_default', 'orm_default', [
            'doctrine_commented_types' => [$type],
        ])->reveal());

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
        ])->reveal());

        $this->assertTrue($connection->getDatabasePlatform()->hasDoctrineTypeMappingFor('foo'));
    }

    public function testCustomPlatform() : void
    {
        $factory = new ConnectionFactory();

        $config = [
            'params' => ['platform' => 'custom.platform'],
        ];

        $container = $this->buildContainer('orm_default', 'orm_default', 'orm_default', $config);

        $platform = $this->prophesize(AbstractPlatform::class);

        $container->get('custom.platform')->willReturn($platform);

        $connection = $factory($container->reveal());

        $this->assertSame($platform->reveal(), $connection->getDatabasePlatform());
    }

    /**
     * @param mixed[] $config
     *
     * @return ContainerInterface|ObjectProphecy
     */
    private function buildContainer(
        string $ownKey = 'orm_default',
        string $configurationKey = 'orm_default',
        string $eventManagerKey = 'orm_default',
        array $config = []
    ) {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'doctrine' => [
                'connection' => [
                    $ownKey => $config + [
                        'driver_class' => PDOSqliteDriver::class,
                    ],
                ],
                'types' => [
                    'custom_type' => BooleanType::class,
                ],
            ],
        ]);

        $container->has(sprintf('doctrine.configuration.%s', $configurationKey))->willReturn(true);
        $container->get(sprintf('doctrine.configuration.%s', $configurationKey))->willReturn($this->configuration);
        $container->has(sprintf('doctrine.event_manager.%s', $eventManagerKey))->willReturn(true);
        $container->get(sprintf('doctrine.event_manager.%s', $eventManagerKey))->willReturn($this->eventManger);

        return $container;
    }
}
