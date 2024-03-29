<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RoaveTest\PsrContainerDoctrine\TestAsset\StubFactory;

final class AbstractFactoryTest extends TestCase
{
    public function testDefaultConfigKey(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new StubFactory();
        self::assertSame('orm_default', $factory($container));
    }

    public function testCustomConfigKey(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new StubFactory('orm_other');
        self::assertSame('orm_other', $factory($container));
    }

    /**
     * @param int[]          $expectedResult
     * @param int[][][]|null $config
     *
     * @dataProvider configProvider
     */
    public function testRetrieveConfig(string $configKey, string $section, array $expectedResult, array|null $config = null): void
    {
        $container = $this->createMock(ContainerInterface::class);

        if ($config === null) {
            $container->expects($this->once())->method('has')->with('config')->willReturn(false);
        } else {
            $container->expects($this->once())->method('has')->with('config')->willReturn(true);
            $container->expects($this->once())->method('get')->with('config')->willReturn($config);
        }

        $actualResult = (new StubFactory())->publicRetrieveConfig($container, $configKey, $section);

        self::assertSame($expectedResult, $actualResult);
    }

    /** @return array<string, mixed> */
    public static function configProvider(): array
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
