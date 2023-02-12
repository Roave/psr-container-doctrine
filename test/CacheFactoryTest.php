<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\Common\Cache\Cache;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\Cache\NullCache;
use Roave\PsrContainerDoctrine\CacheFactory;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;
use stdClass;

/**
 * @coversDefaultClass \Roave\PsrContainerDoctrine\CacheFactory
 */
final class CacheFactoryTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testExtendsAbstractFactory(): void
    {
        self::assertInstanceOf(AbstractFactory::class, new CacheFactory());
    }

    /**
     * @covers ::createWithConfig
     */
    public function testThrowsForMissingConfigKey(): void
    {
        $container = $this->createContainerMockWithConfig(
            [
                'doctrine' => [
                    'cache' => [],
                ],
            ]
        );

        $factory = new CacheFactory('foo');
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Missing "class" config key');
        $factory($container);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createContainerMockWithConfig(array $config): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->with('config')->willReturn(true);
        $container->expects($this->once())->method('get')->with('config')->willReturn($config);

        return $container;
    }

    public function testCanRetrieveCacheItemPoolFromContainer(): void
    {
        $containerId = 'ContainerId';

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [$containerId, true],
            ]);

        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $container
            ->method('get')
            ->willReturnMap([
                ['config', ['doctrine' => ['cache' => ['foo' => ['class' => $containerId]]]]],
                [$containerId, $cacheItemPool],
            ]);

        $factory = new CacheFactory('foo');
        self::assertSame($cacheItemPool, $factory($container));
    }

    public function testThrowsWhenRetrieveFromContainerUnexpectedReturnType(): void
    {
        $containerId = 'ContainerId';

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [$containerId, true],
            ]);

        $unsupportedReturnType = $this->createMock(stdClass::class);
        $container
            ->method('get')
            ->willReturnMap([
                ['config', ['doctrine' => ['cache' => ['foo' => ['class' => $containerId]]]]],
                [$containerId, $unsupportedReturnType],
            ]);

        self::expectExceptionObject(InvalidArgumentException::fromUnsupportedCache($unsupportedReturnType));

        $factory = new CacheFactory('foo');
        $factory($container);
    }

    public function testCanInstantiateCacheItemPoolFromClassName(): void
    {
        $mock      = $this->createMock(Cache::class);
        $className = $mock::class;

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [$className, false],
            ]);

        $container
            ->method('get')
            ->with('config')
            ->willReturn(
                ['doctrine' => ['cache' => ['foo' => ['class' => $className]]]]
            );

        $factory = new CacheFactory('foo');
        self::assertInstanceOf($className, $factory($container));
    }

    public function testThrowsWhenInstantiateUnexpectedReturnType(): void
    {
        $mock      = $this->createMock(stdClass::class);
        $className = $mock::class;

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [$className, false],
            ]);

        $container
            ->method('get')
            ->with('config')
            ->willReturn(
                ['doctrine' => ['cache' => ['foo' => ['class' => $className]]]]
            );

        self::expectExceptionObject(InvalidArgumentException::fromUnsupportedCache($mock));

        $factory = new CacheFactory('foo');
        $factory($container);
    }

    public function testCanInstantiateBundledNullCacheWithoutConfig(): void
    {
        $bundledClassName = NullCache::class;

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [$bundledClassName, false],
            ]);

        $container
            ->method('get')
            ->with('config')
            ->willReturn([]);

        $factory = new CacheFactory($bundledClassName);
        self::assertInstanceOf($bundledClassName, $factory($container));
    }
}
