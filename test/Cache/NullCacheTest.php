<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\Cache;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Roave\PsrContainerDoctrine\Cache\NullCache;

use function array_shift;

final class NullCacheTest extends TestCase
{
    private NullCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new NullCache();
    }

    public function testGetItemReturnsMismatchNullValueItem(): void
    {
        $actual = $this->cache->getItem('testKey');
        self::assertEquals('testKey', $actual->getKey());
        self::assertFalse($actual->isHit());
        self::assertNull($actual->get());
    }

    public function testGetItemsReturnsMismatchNullValueItemForEachKey(): void
    {
        $keys = ['testKey1', 'testKey2'];

        $actual = $this->cache->getItems($keys);
        foreach ($actual as $actualKey => $actualItem) {
            self::assertInstanceOf(CacheItemInterface::class, $actualItem);
            $expectedKey = array_shift($keys);
            self::assertEquals($expectedKey, $actualKey);
            self::assertEquals($expectedKey, $actualItem->getKey());
            self::assertFalse($actualItem->isHit());
            self::assertNull($actualItem->get());
        }
    }
}
