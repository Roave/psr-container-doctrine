<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Cache;

use DateInterval;
use DateTimeInterface;
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

    public function expiresAt(DateTimeInterface|null $expiration): static
    {
        return $this;
    }

    public function expiresAfter(int|DateInterval|null $time): static
    {
        return $this;
    }
}
