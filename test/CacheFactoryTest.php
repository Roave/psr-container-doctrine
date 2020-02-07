<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\CacheFactory;

/**
 * @coversDefaultClass \Roave\PsrContainerDoctrine\CacheFactory
 */
class CacheFactoryTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testExtendsAbstractFactory() : void
    {
        $this->assertInstanceOf(AbstractFactory::class, new CacheFactory());
    }

    /**
     * @covers ::createWithConfig
     */
    public function testFileSystemCacheConstructor() : void
    {
        $config = [
            'doctrine' => [
                'cache' => [
                    'filesystem' => [
                        'class'     => FilesystemCache::class,
                        'directory' => 'test',
                    ],
                ],
            ],
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)->onlyMethods(['has', 'get'])->getMock();
        $container->expects($this->once())->method('has')->with('config')->willReturn(true);
        $container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $factory       = new CacheFactory('filesystem');
        $cacheInstance = $factory($container);

        $this->assertInstanceOf(FilesystemCache::class, $cacheInstance);
    }

    public function testCacheChainContainsInitializedProviders() : void
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

        $container = $this->getMockBuilder(ContainerInterface::class)->onlyMethods(['has', 'get'])->getMock();
        $container->method('has')
            ->withConsecutive(['config'], ['config'], [ArrayCache::class], ['config'], [ArrayCache::class])
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $container->method('get')->with('config')->willReturn($config);

        $factory       = new CacheFactory('chain');
        $cacheInstance = $factory($container);

        $this->assertInstanceOf(ChainCache::class, $cacheInstance);
    }
}
