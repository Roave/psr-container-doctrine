<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\ORM\Events;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\EventManagerFactory;
use Roave\PsrContainerDoctrine\Exception\DomainException;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;
use RoaveTest\PsrContainerDoctrine\TestAsset\StubEventListener;
use RoaveTest\PsrContainerDoctrine\TestAsset\StubEventSubscriber;
use stdClass;

use function array_pop;
use function sprintf;

final class EventManagerFactoryTest extends TestCase
{
    public function testDefaults(): void
    {
        $factory      = new EventManagerFactory();
        $container    = $this->createMock(ContainerInterface::class);
        $eventManager = $factory($container);

        self::assertCount(0, $eventManager->getAllListeners());
    }

    public function testInvalidInstanceSubscriber(): void
    {
        $factory = new EventManagerFactory();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid event subscriber "stdClass" given');

        $factory($this->buildContainer(new stdClass()));
    }

    public function testInvalidTypeSubscriber(): void
    {
        $factory = new EventManagerFactory();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid event subscriber "integer" given');

        $factory($this->buildContainer(1));
    }

    public function testInvalidStringSubscriber(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['config'], ['NonExistentClass'])
            ->willReturnOnConsecutiveCalls(true, false);
        $container->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn($this->getConfigForSubscriber('NonExistentClass'));
        $factory = new EventManagerFactory();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid event subscriber "NonExistentClass" given');

        $factory($container);
    }

    public function testInstanceSubscriber(): void
    {
        $factory      = new EventManagerFactory();
        $eventManager = $factory($this->buildContainer(new StubEventSubscriber()));

        self::assertCount(1, $eventManager->getListeners('foo'));
    }

    public function testClassNameSubscriber(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['config'], [StubEventSubscriber::class])
            ->willReturnOnConsecutiveCalls(true, false);
        $container->method('get')
            ->with('config')
            ->willReturn($this->getConfigForSubscriber(StubEventSubscriber::class));

        $factory      = new EventManagerFactory();
        $eventManager = $factory($container);

        self::assertCount(1, $eventManager->getListeners('foo'));
    }

    public function testServiceNameSubscriber(): void
    {
        $eventSubscriber = new StubEventSubscriber();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['config'], [StubEventSubscriber::class])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['config'], [StubEventSubscriber::class])
            ->willReturnOnConsecutiveCalls($this->getConfigForSubscriber(StubEventSubscriber::class), $eventSubscriber);

        $factory      = new EventManagerFactory();
        $eventManager = $factory($container);
        $listeners    = $eventManager->getListeners('foo');

        self::assertCount(1, $listeners);
        self::assertSame($eventSubscriber, array_pop($listeners));
    }

    public function testInvalidTypeListener(): void
    {
        $factory = new EventManagerFactory();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid event listener config: must be an array');
        $factory($this->buildContainerWithListener(1));
    }

    public function testInvalidStringListener(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['config'], ['NonExistentClass'])
            ->willReturn(true, false);
        $container->method('get')
            ->with('config')
            ->willReturn($this->getConfigForListener(['listener' => 'NonExistentClass']));

        $factory = new EventManagerFactory();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid event listener "NonExistentClass" given');
        $factory($container);
    }

    public function testInvalidEventNameListener(): void
    {
        $container = $this->buildContainerWithListener([
            'events' => [Events::onFlush, 'foo'],
            'listener' => new StubEventListener(),
        ]);

        $factory = new EventManagerFactory();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid event listener "%s" given: must have a "foo" method',
            StubEventListener::class
        ));
        $factory($container);
    }

    public function testInstanceListener(): void
    {
        $factory      = new EventManagerFactory();
        $eventManager = $factory($this->buildContainerWithListener([
            'events' => Events::onFlush,
            'listener' => new StubEventListener(),
        ]));

        self::assertCount(1, $eventManager->getListeners(Events::onFlush));
    }

    public function testClassNameListener(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['config'], [StubEventListener::class])
            ->willReturn(true, false);
        $container->method('get')
            ->with('config')
            ->willReturn(
                $this->getConfigForListener(
                    [
                        'events' => Events::onFlush,
                        'listener' => StubEventListener::class,
                    ]
                )
            );

        $factory      = new EventManagerFactory();
        $eventManager = $factory($container);

        self::assertCount(1, $eventManager->getListeners(Events::onFlush));
    }

    public function testServiceNameListener(): void
    {
        $eventListener = new StubEventListener();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['config'], [StubEventListener::class])
            ->willReturn(true, true);
        $container->method('get')
            ->withConsecutive(['config'], [StubEventListener::class])
            ->willReturn(
                $this->getConfigForListener(
                    [
                        'events' => Events::onFlush,
                        'listener' => StubEventListener::class,
                    ]
                ),
                $eventListener
            );

        $factory      = new EventManagerFactory();
        $eventManager = $factory($container);
        $listeners    = $eventManager->getListeners(Events::onFlush);

        self::assertCount(1, $listeners);
        self::assertSame($eventListener, array_pop($listeners));
    }

    /**
     * @param mixed $subscriber
     */
    private function buildContainer($subscriber): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('config')->willReturn(true);
        $container->method('get')->with('config')->willReturn(
            [
                'doctrine' => [
                    'event_manager' => [
                        'orm_default' => [
                            'subscribers' => [$subscriber],
                        ],
                    ],
                ],
            ]
        );

        return $container;
    }

    /**
     * @param mixed $listener
     */
    private function buildContainerWithListener($listener): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('config')->willReturn(true);
        $container->method('get')->with('config')->willReturn($this->getConfigForListener($listener));

        return $container;
    }

    /**
     * @param mixed $subscriber
     *
     * @return array<string, mixed>
     */
    private function getConfigForSubscriber($subscriber): array
    {
        return [
            'doctrine' => [
                'event_manager' => [
                    'orm_default' => [
                        'subscribers' => [$subscriber],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param mixed $listener
     *
     * @return array<string, mixed>
     */
    private function getConfigForListener($listener): array
    {
        return [
            'doctrine' => [
                'event_manager' => [
                    'orm_default' => [
                        'listeners' => [$listener],
                    ],
                ],
            ],
        ];
    }
}
