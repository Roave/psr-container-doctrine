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

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory       = new CacheFactory('filesystem');
        $cacheInstance = $factory($container->reveal());

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

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);
        $container->has(ArrayCache::class)->willReturn(false);

        $factory       = new CacheFactory('chain');
        $cacheInstance = $factory($container->reveal());

        $this->assertInstanceOf(ChainCache::class, $cacheInstance);
    }
}
