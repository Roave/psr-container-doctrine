<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Exception;

use function sprintf;

final class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
    public static function forMissingConfigKey(string $key): self
    {
        return new self(sprintf('Missing "%s" config key', $key));
    }
}
