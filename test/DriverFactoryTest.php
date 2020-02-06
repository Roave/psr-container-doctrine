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

class DriverFactoryTest extends TestCase
{
    public function testMissingClassKeyWillReturnOutOfBoundException() : void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $factory   = new DriverFactory();

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Missing "class" config key');

        $factory($container->reveal());
    }

    public function testItSupportsGlobalBasenameOptionOnFileDrivers() : void
    {
        $globalBasename = 'foobar';

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'doctrine' => [
                'driver' => [
                    'orm_default' => [
                        'class' => TestAsset\StubFileDriver::class,
                        'global_basename' => $globalBasename,
                    ],
                ],
            ],
        ]);

        $factory = new DriverFactory();

        $driver = $factory($container->reveal());
        $this->assertInstanceOf(FileDriver::class, $driver);
        $this->assertSame($globalBasename, $driver->getGlobalBasename());
    }

    /**
     * @param string|class-string $driverClass
     *
     * @dataProvider simplifiedDriverClassProvider
     */
    public function testItSupportsSettingExtensionInDriversUsingSymfonyFileLocator(string $driverClass) : void
    {
        $extension = '.foo.bar';

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'doctrine' => [
                'driver' => [
                    'orm_default' => [
                        'class' => $driverClass,
                        'extension' => $extension,
                    ],
                ],
            ],
        ]);

        $driver = (new DriverFactory())->__invoke($container->reveal());
        $this->assertInstanceOf(FileDriver::class, $driver);
        $this->assertSame($extension, $driver->getLocator()->getFileExtension());
    }

    /** @return string[][] */
    public function simplifiedDriverClassProvider() : array
    {
        return [
            [ Driver\SimplifiedXmlDriver::class ],
            [ Driver\SimplifiedYamlDriver::class ],
        ];
    }

    public function testItSupportsSettingDefaultDriverUsingMappingDriverChain() : void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
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
        ]);

        $factory = new DriverFactory();

        $driver = $factory($container->reveal());
        $this->assertInstanceOf(MappingDriverChain::class, $driver);
        $this->assertInstanceOf(TestAsset\StubFileDriver::class, $driver->getDefaultDriver());
    }
}
