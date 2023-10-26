<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Exception;

use Doctrine\Common\Cache\Cache;
use Psr\Cache\CacheItemPoolInterface;

use function gettype;
use function is_object;
use function sprintf;

final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function forInvalidEventListenerConfig(mixed $listenerConfig): self
    {
        return new self(
            sprintf(
                'Invalid event listener config: must be an array, "%s" given',
                gettype($listenerConfig),
            ),
        );
    }

    public static function fromUnsupportedCache(mixed $unsupportedCache): self
    {
        return new self(
            sprintf(
                'Invalid cache type provided. Either an implementation of "%s" or "%s" is supported. Got: "%s"',
                CacheItemPoolInterface::class,
                Cache::class,
                is_object($unsupportedCache) ? $unsupportedCache::class : gettype($unsupportedCache),
            ),
        );
    }
}
