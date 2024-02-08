<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Exception;

use Psr\Cache\CacheItemPoolInterface;

use function get_debug_type;
use function gettype;
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
                'Invalid cache type provided. Implementation of "%s" is supported. Got: "%s"',
                CacheItemPoolInterface::class,
                get_debug_type($unsupportedCache),
            ),
        );
    }
}
