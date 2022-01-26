<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\ORM;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\RuntimeException;
use Roave\PsrContainerDoctrine\ORM\ContainerEntityListenerResolver;
use stdClass;

class ContainerEntityListenerResolverTest extends TestCase
{
    private function createContainerMockWithConfig(object $listener): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->with('foo-listener')->willReturn($listener);

        return $container;
    }

    public function testResolveListener(): void
    {
        $fooListener = new stdClass();
        $container   = $this->createContainerMockWithConfig($fooListener);

        $resolver = new ContainerEntityListenerResolver($container);
        $listener = $resolver->resolve('foo-listener');

        $this->assertEquals($fooListener, $listener);
    }

    /**
     * @return string[][]
     */
    public function classMethods(): array
    {
        return [
            ['clear'],
            ['register'],
        ];
    }

    /**
     * @dataProvider classMethods
     */
    public function testCallClearRegisterFailed(string $method): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $resolver  = new ContainerEntityListenerResolver($container);
        $this->expectException(RuntimeException::class);
        $resolver->{$method}('foo-listener');
    }
}
