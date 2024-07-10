<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use ReflectionProperty;
use Roave\PsrContainerDoctrine\ConfigurationFactory;
use TypeError;

use function in_array;

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
            ->willReturnMap([
                ['config', true],
                ['doctrine.cache.metadata', true],
                ['doctrine.cache.query', true],
                ['doctrine.cache.result', true],
                ['doctrine.cache.hydration', true],
                ['doctrine.driver.orm_default', true],
            ]);

        $container
            ->method('get')
            ->willReturnMap([
                ['config', $config],
                ['doctrine.cache.metadata', $metadataCache],
                ['doctrine.cache.query', $queryCache],
                ['doctrine.cache.result', $resultCache],
                ['doctrine.cache.hydration', $hydrationCache],
                ['doctrine.driver.orm_default', $mappingDriver],
            ]);

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
                            'acme.middleware.foo',
                            'acme.middleware.bar',
                        ],
                    ],
                ],
            ],
        ];

        $container = $this->createStub(ContainerInterface::class);

        $container
            ->method('has')
            ->willReturnCallback(
                static function (string $id): bool {
                    return in_array(
                        $id,
                        [
                            'config',
                            'doctrine.driver.orm_default',
                            'acme.middleware.foo',
                            'acme.middleware.bar',
                        ],
                        true,
                    );
                },
            );

        $container
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    ['doctrine.driver.orm_default', $this->createStub(MappingDriver::class)],
                    ['acme.middleware.foo', $middlewareFoo],
                    ['acme.middleware.bar', $middlewareBar],
                ],
            );

        $configuration = (new ConfigurationFactory())($container);

        self::assertSame([$middlewareFoo, $middlewareBar], $configuration->getMiddlewares());
    }

    public function testWillSetSchemaAssetsFilterByContainerId(): void
    {
        $testFilter = static fn (): bool => true;
        $config     = [
            'doctrine' => [
                'configuration' => [
                    'orm_default' => ['schema_assets_filter' => 'testFilterContainerId'],
                ],
            ],
        ];

        $container = $this->createStub(ContainerInterface::class);

        $container
            ->method('has')
            ->willReturnCallback(
                static fn (string $id) => in_array(
                    $id,
                    [
                        'config',
                        'doctrine.driver.orm_default',
                        'testFilterContainerId',
                    ],
                    true,
                ),
            );

        $container
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    ['doctrine.driver.orm_default', $this->createStub(MappingDriver::class)],
                    ['testFilterContainerId', $testFilter],
                ],
            );

        $configuration = (new ConfigurationFactory())($container);

        self::assertSame($testFilter, $configuration->getSchemaAssetsFilter());
    }

    public function testMistypeInSchemaAssetsFilterResolvedContainerId(): void
    {
        $testFilter = ['misconfig' => 'resolved service is not callable'];
        $config     = [
            'doctrine' => [
                'configuration' => [
                    'orm_default' => ['schema_assets_filter' => 'testFilterContainerId'],
                ],
            ],
        ];

        $container = $this->createStub(ContainerInterface::class);

        $container
            ->method('has')
            ->willReturnCallback(
                static fn (string $id) => in_array(
                    $id,
                    [
                        'config',
                        'doctrine.driver.orm_default',
                        'testFilterContainerId',
                    ],
                    true,
                ),
            );

        $container
            ->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                    ['doctrine.driver.orm_default', $this->createStub(MappingDriver::class)],
                    ['testFilterContainerId', $testFilter],
                ],
            );

        $this->expectException(TypeError::class);
        if (InstalledVersions::satisfies(new VersionParser(), 'doctrine/dbal', '^3.8')) {
            $this->expectExceptionMessage('Doctrine\DBAL\Configuration::setSchemaAssetsFilter(): Argument #1 ($callable) must be of type ?callable, array given,');
        } else {
            $this->expectExceptionMessage('Doctrine\DBAL\Configuration::setSchemaAssetsFilter(): Argument #1 ($schemaAssetsFilter) must be of type callable, array given');
        }

        (new ConfigurationFactory())($container);
    }

    /** @param non-empty-string $propertyName */
    private function exctractPropertyValue(object $object, string $propertyName): mixed
    {
        return (new ReflectionProperty($object, $propertyName))->getValue($object);
    }
}
