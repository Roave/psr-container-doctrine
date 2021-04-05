<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;
use RoaveTest\PsrContainerDoctrine\TestAsset\StubFactory;

final class AbstractFactoryTest extends TestCase
{
    public function testDefaultConfigKey(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new StubFactory();
        $this->assertSame('orm_default', $factory($container));
    }

    public function testCustomConfigKey(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new StubFactory('orm_other');
        $this->assertSame('orm_other', $factory($container));
    }

    public function testStaticCall(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $this->assertSame('orm_other', StubFactory::orm_other($container));
    }

    public function testStaticCallWithoutContainer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The first argument must be of type Psr\Container\ContainerInterface');
        StubFactory::orm_other();
    }

    /**
     * @param int[]          $expectedResult
     * @param int[][][]|null $config
     *
     * @dataProvider configProvider
     */
    public function testRetrieveConfig(string $configKey, string $section, array $expectedResult, ?array $config = null): void
    {
        $container = $this->createMock(ContainerInterface::class);

        if ($config === null) {
            $container->expects($this->once())->method('has')->with('config')->willReturn(false);
        } else {
            $container->expects($this->once())->method('has')->with('config')->willReturn(true);
            $container->expects($this->once())->method('get')->with('config')->willReturn($config);
        }

        $actualResult = (new StubFactory())->retrieveConfig($container, $configKey, $section);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array<string, mixed>
     */
    public function configProvider(): array
    {
        return [
            'no-config' => ['foo', 'bar', [], null],
            'doctrine-missing' => ['foo', 'bar', [], []],
            'section-missing' => ['foo', 'bar', [], ['doctrine' => []]],
            'config-key-missing' => ['foo', 'bar', [], ['doctrine' => ['bar' => []]]],
            'config-key-exists' => ['foo', 'bar', [1], ['doctrine' => ['bar' => ['foo' => [1]]]]],
        ];
    }
}
