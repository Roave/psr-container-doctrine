<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\ORM\Mapping\Driver;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\DriverFactory;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;

final class DriverFactoryTest extends TestCase
{
    public function testMissingClassKeyWillReturnOutOfBoundException(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new DriverFactory();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Missing "class" config key');

        $factory($container);
    }

    public function testItSupportsGlobalBasenameOptionOnFileDrivers(): void
    {
        $globalBasename = 'foobar';

        $container = $this->createContainerMockWithConfig(
            [
                'doctrine' => [
                    'driver' => [
                        'orm_default' => [
                            'class' => TestAsset\StubFileDriver::class,
                            'global_basename' => $globalBasename,
                        ],
                    ],
                ],
            ]
        );

        $driver = (new DriverFactory())->__invoke($container);
        $this->assertInstanceOf(FileDriver::class, $driver);
        $this->assertSame($globalBasename, $driver->getGlobalBasename());
    }

    /**
     * @psalm-param class-string<FileDriver> $driverClass
     * @dataProvider simplifiedDriverClassProvider
     */
    public function testItSupportsSettingExtensionInDriversUsingSymfonyFileLocator(string $driverClass): void
    {
        $extension = '.foo.bar';

        $container = $this->createContainerMockWithConfig(
            [
                'doctrine' => [
                    'driver' => [
                        'orm_default' => [
                            'class' => $driverClass,
                            'extension' => $extension,
                        ],
                    ],
                ],
            ]
        );

        $driver = (new DriverFactory())->__invoke($container);
        $this->assertInstanceOf(FileDriver::class, $driver);
        $this->assertSame($extension, $driver->getLocator()->getFileExtension());
    }

    /**
     * @return string[][]
     *
     * @psalm-return list<list<class-string<FileDriver>>>
     */
    public function simplifiedDriverClassProvider(): array
    {
        return [
            [ Driver\SimplifiedXmlDriver::class ],
            [ Driver\SimplifiedYamlDriver::class ],
        ];
    }

    public function testMappingDriverChainIsCreatedWithNoDefaultDriverWhenDefaultDriverNotSpecified(): void
    {
        $container = $this->createContainerMockWithConfig(
            [
                'doctrine' => [
                    'driver' => [
                        'orm_default' => [
                            'class' => MappingDriverChain::class,
                        ],
                    ],
                ],
            ],
            1
        );

        $driver = (new DriverFactory())->__invoke($container);
        self::assertInstanceOf(MappingDriverChain::class, $driver);
        self::assertNull($driver->getDefaultDriver());
    }

    public function testItSupportsSettingDefaultDriverUsingMappingDriverChain(): void
    {
        $container = $this->createContainerMockWithConfig(
            [
                'doctrine' => [
                    'driver' => [
                        'orm_default' => [
                            'class' => MappingDriverChain::class,
                            'default_driver' => 'orm_stub',
                        ],
                        'orm_stub' => [
                            'class' => TestAsset\StubFileDriver::class,
                        ],
                    ],
                ],
            ],
            2
        );

        $driver = (new DriverFactory())->__invoke($container);
        $this->assertInstanceOf(MappingDriverChain::class, $driver);
        $this->assertInstanceOf(TestAsset\StubFileDriver::class, $driver->getDefaultDriver());
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createContainerMockWithConfig(array $config, int $expectedCalls = 1): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly($expectedCalls))->method('has')->with('config')->willReturn(true);
        $container->expects($this->exactly($expectedCalls))->method('get')->with('config')->willReturn($config);

        return $container;
    }
}
