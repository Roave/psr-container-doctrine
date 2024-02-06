<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Exception;

use function sprintf;

final class DomainException extends \DomainException implements ExceptionInterface
{
    public static function forInvalidMigrationsCommand(string $command): self
    {
        return new self(
            sprintf(
                'Requested class "%s" is not a valid doctrine migrations command',
                $command,
            ),
        );
    }
}
