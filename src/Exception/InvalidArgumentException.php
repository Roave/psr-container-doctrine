<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Exception;

use function gettype;
use function sprintf;

final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $listenerConfig
     */
    public static function forInvalidEventListenerConfig($listenerConfig) : self
    {
        return new self(
            sprintf(
                'Invalid event listener config: must be an array, "%s" given',
                gettype($listenerConfig)
            )
        );
    }
}
