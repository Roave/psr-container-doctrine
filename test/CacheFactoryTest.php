<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\CacheFactory;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;

use function extension_loaded;

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
        $this->assertInstanceOf(AbstractFactory::class, new CacheFactory());
    }

    /**
     * @covers ::createWithConfig
     */
    public function testFileSystemCacheConstructor(): void
    {
        $container = $this->createContainerMockWithConfig(
            [
                'doctrine' => [
                    'cache' => [
                        'filesystem' => [
                            'class' => FilesystemCache::class,
                            'directory' => 'test',
                        ],
                    ],
                ],
            ]
        );

        $factory       = new CacheFactory('filesystem');
        $cacheInstance = $factory($container);

        $this->assertInstanceOf(FilesystemCache::class, $cacheInstance);
    }

    public function testCacheChainContainsInitializedProviders(): void
    {
        $config = [
            'doctrine' => [
                'cache' => [
                    'chain' => [
                        'class'     => ChainCache::class,
                        'providers' => ['array', 'array'],
                    ],
                ],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->withConsecutive(['config'], ['config'], [ArrayCache::class], ['config'], [ArrayCache::class])
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $container->method('get')->with('config')->willReturn($config);

        $factory       = new CacheFactory('chain');
        $cacheInstance = $factory($container);

        $this->assertInstanceOf(ChainCache::class, $cacheInstance);
    }

    public function testCanInjectWrappedInstances(): void
    {
        if (! extension_loaded('memcached')) {
            $this->markTestSkipped('Extension memcached is not loaded');
        }

        /** @psalm-suppress ArgumentTypeCoercion \Memcached needs to be imported otherwise */
        $wrappedMemcached = $this->createMock('Memcached');

        $config = [
            'doctrine' => [
                'cache' => [
                    'memcached' => [
                        'class'     => MemcachedCache::class,
                        'instance'  => $wrappedMemcached,
                        'namespace' => 'foo',
                    ],
                ],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->withConsecutive(['config'], [MemcachedCache::class])
            ->willReturnOnConsecutiveCalls(true, false);
        $container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $factory  = new CacheFactory('memcached');
        $instance = $factory($container);

        $this->assertInstanceOf(MemcachedCache::class, $instance);
        $this->assertSame($wrappedMemcached, $instance->getMemcached());
        $this->assertSame('foo', $instance->getNamespace());
    }

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
}
