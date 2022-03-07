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
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;
use stdClass;

use function array_key_exists;

final class ConfigurationFactoryTest extends TestCase
{
    public function testWillSetCacheItemPoolCaches(): void
    {
        $resultCache    = $this->createMock(CacheItemPoolInterface::class);
        $queryCache     = $this->createMock(CacheItemPoolInterface::class);
        $metadataCache  = $this->createMock(CacheItemPoolInterface::class);
        $hydrationCache = $this->createMock(CacheItemPoolInterface::class);

        $config = [
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

        $container = $this->buildContainerWithServicesAndDefaultMappingDriver(
            [
                'config'                   => $config,
                'doctrine.cache.metadata'  => $metadataCache,
                'doctrine.cache.query'     => $queryCache,
                'doctrine.cache.result'    => $resultCache,
                'doctrine.cache.hydration' => $hydrationCache,
            ]
        );

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

        $container = $this->buildContainerWithServicesAndDefaultMappingDriver(
            [
                'config'              => $config,
                'acme.middleware.bar' => $middlewareBar,
            ]
        );

        $configuration = (new ConfigurationFactory())($container);

        self::assertSame([$middlewareFoo, $middlewareBar], $configuration->getMiddlewares());
    }

    public function testInvalidMiddlewareThrowsException(): void
    {
        $invalidValueForAMiddleware = new stdClass();
        $config                     = [
            'doctrine' => [
                'configuration' => [
                    'orm_default' => [
                        'middlewares' => [$invalidValueForAMiddleware],
                    ],
                ],
            ],
        ];

        $container = $this->buildContainerWithServicesAndDefaultMappingDriver(['config' => $config]);

        $subject = new ConfigurationFactory();

        $this->expectException(InvalidArgumentException::class);
        ($subject)($container);
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

    /**
     * @param array<string, mixed> $services
     */
    private function buildContainerWithServicesAndDefaultMappingDriver(array $services): ContainerInterface
    {
        $services['doctrine.driver.orm_default'] = $this->createStub(MappingDriver::class);
        $container                               = $this->createStub(ContainerInterface::class);
        $container
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($services): bool {
                    return array_key_exists($id, $services);
                }
            );
        $container
            ->method('get')
            ->willReturnCallback(
            /**
             * @return mixed
             */
                static function (string $id) use ($services) {
                    return $services[$id];
                }
            );

        return $container;
    }
}
