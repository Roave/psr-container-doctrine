<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class NullCache implements CacheItemPoolInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItem($key): CacheItemInterface
    {
        return new NullCacheItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->getItem($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key): bool
    {
        return false;
    }

    public function clear(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): bool
    {
        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return true;
    }

    public function commit(): bool
    {
        return true;
    }
}
