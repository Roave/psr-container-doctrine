<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\DBAL\Driver\Middleware;
use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use ReflectionProperty;
use Roave\PsrContainerDoctrine\ConfigurationFactory;

final class ConfigurationFactoryTest extends TestCase
{
    public function testWillSetCacheItemPoolCaches(): void
    {
        $resultCache    = $this->createMock(CacheItemPoolInterface::class);
        $queryCache     = $this->createMock(CacheItemPoolInterface::class);
        $metadataCache  = $this->createMock(CacheItemPoolInterface::class);
        $hydrationCache = $this->createMock(CacheItemPoolInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $config    = [
            'doctrine' => [
                'configuration' => [
                    'orm_default' => [
                        'metadata_cache' => 'metadata',
                        'result_cache' => 'result',
                        'query_cache' => 'query',
                        'hydration_cache' => 'hydration',
                        'second_level_cache' => ['enabled' => true],
                    ],
                ],
            ],
        ];

        $mappingDriver = $this->createMock(MappingDriver::class);

        $container
            ->method('has')
            ->withConsecutive(['config'], ['doctrine.cache.metadata'], ['doctrine.cache.query'], ['doctrine.cache.result'], ['doctrine.cache.hydration'], ['doctrine.driver.orm_default'])
            ->willReturnOnConsecutiveCalls(true, true, true, true, true, true);

        $container
            ->method('get')
            ->withConsecutive(['config'], ['doctrine.cache.metadata'], ['doctrine.cache.query'], ['doctrine.cache.result'], ['doctrine.cache.hydration'], ['doctrine.driver.orm_default'])
            ->willReturnOnConsecutiveCalls($config, $metadataCache, $queryCache, $resultCache, $hydrationCache, $mappingDriver);

        $configuration = (new ConfigurationFactory())($container);

        self::assertSame($resultCache, $configuration->getResultCache());
        self::assertSame($queryCache, $configuration->getQueryCache());
        self::assertSame($metadataCache, $configuration->getMetadataCache());
        self::assertSame($hydrationCache, $configuration->getHydrationCache());

        $secondLevelCacheConfiguration = $configuration->getSecondLevelCacheConfiguration();
        self::assertInstanceOf(CacheConfiguration::class, $secondLevelCacheConfiguration);
        $secondLevelCacheFactory = $secondLevelCacheConfiguration->getCacheFactory();
        self::assertInstanceOf(DefaultCacheFactory::class, $secondLevelCacheFactory);
        self::assertSame($resultCache, $this->exctractPropertyValue($secondLevelCacheFactory, 'cacheItemPool'));
    }

    public function testWillSetMiddlewares(): void
    {
        $middlewareFoo = $this->createStub(Middleware::class);
        $middlewareBar = $this->createStub(Middleware::class);
        $config        = [
            'doctrine' => [
                'configuration' => [
                    'orm_default' => [
                        'middlewares' => [
                            $middlewareFoo,
                            'acme.middleware.bar',
                        ],
                    ],
                ],
            ],
        ];

        $container = $this->createStub(ContainerInterface::class);
        $container
            ->method('has')
            ->willReturnMap(
                [
                    ['config', true],
                    ['acme.middleware.bar', true],
                    ['doctrine.driver.orm_default', true],
                ]
            );
        $container
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    ['acme.middleware.bar', $middlewareBar],
                    ['doctrine.driver.orm_default', $this->createStub(MappingDriver::class)],
                ]
            );

        $configuration = (new ConfigurationFactory())($container);

        self::assertSame([$middlewareFoo, $middlewareBar], $configuration->getMiddlewares());
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return mixed
     */
    private function exctractPropertyValue(object $object, string $propertyName)
    {
        $property = new ReflectionProperty($object, $propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
