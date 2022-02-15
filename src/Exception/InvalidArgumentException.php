<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Exception;

use Doctrine\Common\Cache\Cache;
use Psr\Cache\CacheItemPoolInterface;

use function gettype;
use function sprintf;

final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $listenerConfig
     */
    public static function forInvalidEventListenerConfig($listenerConfig): self
    {
        return new self(
            sprintf(
                'Invalid event listener config: must be an array, "%s" given',
                gettype($listenerConfig)
            )
        );
    }

    /**
     * @param non-empty-string $cacheType
     */
    public static function fromUnsupportedCacheType(string $cacheType): self
    {
        return new self(
            sprintf(
                'Invalid cache type provided. Either an implementation of "%s" or "%s" is supported. Got: "%s"',
                CacheItemPoolInterface::class,
                Cache::class,
                $cacheType
            )
        );
    }
}
