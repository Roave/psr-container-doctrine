<?php
declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\ORM\Events;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\EventManagerFactory;
use Roave\PsrContainerDoctrine\Exception\DomainException;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;
use RoaveTest\PsrContainerDoctrine\TestAsset\StubEventListener;
use RoaveTest\PsrContainerDoctrine\TestAsset\StubEventSubscriber;
use stdClass;

class EventManagerFactoryTest extends TestCase
{
    public function testDefaults() : void
    {
        $factory = new EventManagerFactory();
        $eventManager = $factory($this->prophesize(ContainerInterface::class)->reveal());

        $this->assertSame(0, count($eventManager->getListeners()));
    }

    public function testInvalidInstanceSubscriber() : void
    {
        $factory = new EventManagerFactory();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid event subscriber "stdClass" given');
        $factory($this->buildContainer(new stdClass())->reveal());
    }

    public function testInvalidTypeSubscriber() : void
    {
        $factory = new EventManagerFactory();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid event subscriber "integer" given');
        $factory($this->buildContainer(1)->reveal());
    }

    public function testInvalidStringSubscriber() : void
    {
        $container = $this->buildContainer('NonExistentClass');
        $container->has('NonExistentClass')->willReturn(false);

        $factory = new EventManagerFactory();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid event subscriber "NonExistentClass" given');
        $factory($container->reveal());
    }

    public function testInstanceSubscriber() : void
    {
        $factory = new EventManagerFactory();
        $eventManager = $factory($this->buildContainer(new StubEventSubscriber())->reveal());

        $this->assertSame(1, count($eventManager->getListeners('foo')));
    }

    public function testClassNameSubscriber() : void
    {
        $container = $this->buildContainer(StubEventSubscriber::class);
        $container->has(StubEventSubscriber::class)->willReturn(false);

        $factory = new EventManagerFactory();
        $eventManager = $factory($container->reveal());

        $this->assertSame(1, count($eventManager->getListeners('foo')));
    }

    public function testServiceNameSubscriber() : void
    {
        $eventSubscriber = new StubEventSubscriber();

        $container = $this->buildContainer(StubEventSubscriber::class);
        $container->has(StubEventSubscriber::class)->willReturn(true);
        $container->get(StubEventSubscriber::class)->willReturn($eventSubscriber);

        $factory = new EventManagerFactory();
        $eventManager = $factory($container->reveal());
        $listeners = $eventManager->getListeners('foo');

        $this->assertSame($eventSubscriber, array_pop($listeners));
    }

    public function testInvalidTypeListener() : void
    {
        $factory = new EventManagerFactory();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid event listener config: must be an array');
        $factory($this->buildContainerWithListener(1)->reveal());
    }

    public function testInvalidStringListener() : void
    {
        $container = $this->buildContainerWithListener([ 'listener' => 'NonExistentClass']);
        $container->has('NonExistentClass')->willReturn(false);

        $factory = new EventManagerFactory();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid event listener "NonExistentClass" given');
        $factory($container->reveal());
    }

    public function testInvalidEventNameListener() : void
    {
        $container = $this->buildContainerWithListener([
            'events' => [Events::onFlush, 'foo'],
            'listener' => new StubEventListener()
        ]);

        $factory = new EventManagerFactory();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid event listener "%s" given: must have a "foo" method',
            StubEventListener::class
        ));
        $factory($container->reveal());
    }

    public function testInstanceListener() : void
    {
        $factory = new EventManagerFactory();
        $eventManager = $factory($this->buildContainerWithListener([
            'events' => Events::onFlush,
            'listener' => new StubEventListener()
        ])->reveal());

        $this->assertSame(1, count($eventManager->getListeners(Events::onFlush)));
    }

    public function testClassNameListener() : void
    {
        $container = $this->buildContainerWithListener([
            'events' => Events::onFlush,
            'listener' => StubEventListener::class
        ]);
        $container->has(StubEventListener::class)->willReturn(false);

        $factory = new EventManagerFactory();
        $eventManager = $factory($container->reveal());

        $this->assertSame(1, count($eventManager->getListeners(Events::onFlush)));
    }

    public function testServiceNameListener() : void
    {
        $eventListener = new StubEventListener();

        $container = $this->buildContainerWithListener([
            'events' => Events::onFlush,
            'listener' => StubEventListener::class
        ]);
        $container->has(StubEventListener::class)->willReturn(true);
        $container->get(StubEventListener::class)->willReturn($eventListener);

        $factory = new EventManagerFactory();
        $eventManager = $factory($container->reveal());
        $listeners = $eventManager->getListeners(Events::onFlush);

        $this->assertSame($eventListener, array_pop($listeners));
    }

    /**
     * @param mixed $subscriber
     * @return ContainerInterface|ObjectProphecy
     */
    private function buildContainer($subscriber)
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'doctrine' => [
                'event_manager' => [
                    'orm_default' => [
                        'subscribers' => [
                            $subscriber
                        ],
                    ],
                ],
            ],
        ]);

        return $container;
    }

    /**
     * @param mixed $listener
     * @return ContainerInterface|ObjectProphecy
     */
    private function buildContainerWithListener($listener)
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'doctrine' => [
                'event_manager' => [
                    'orm_default' => [
                        'listeners' => [
                            $listener
                        ],
                    ],
                ],
            ],
        ]);

        return $container;
    }
}
