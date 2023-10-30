<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class NullCache implements CacheItemPoolInterface
{
    /**
     * {@inheritDoc}
     */
    public function getItem($key): CacheItemInterface
    {
        return new NullCacheItem($key);
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(array $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->getItem($key);
        }
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function deleteItem($key): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
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
