<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Cache;

use Psr\Cache\CacheItemInterface;

final class NullCacheItem implements CacheItemInterface
{
    public function __construct(
        private readonly string $key,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return null;
    }

    public function isHit(): bool
    {
        return false;
    }

    public function set(mixed $value): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function expiresAt($expiration): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function expiresAfter($time): static
    {
        return $this;
    }
}
