<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Exception;

use Doctrine\Common\Cache\Cache;
use Psr\Cache\CacheItemPoolInterface;

use function get_class;
use function gettype;
use function is_object;
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
     * @param mixed $unsupportedCache
     */
    public static function fromUnsupportedCache($unsupportedCache): self
    {
        return new self(
            sprintf(
                'Invalid cache type provided. Either an implementation of "%s" or "%s" is supported. Got: "%s"',
                CacheItemPoolInterface::class,
                Cache::class,
                is_object($unsupportedCache) ? get_class($unsupportedCache) : gettype($unsupportedCache)
            )
        );
    }
}
