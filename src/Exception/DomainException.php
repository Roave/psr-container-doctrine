<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\Exception;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Types\TypeProvider;
use Doctrine\DBAL\Types\TypeRegistry;

use function get_debug_type;
use function sprintf;

final class DomainException extends \DomainException implements ExceptionInterface
{
    public static function forMissingMethodOnListener(string $listenerName, string $event): self
    {
        return new self(
            sprintf(
                'Invalid event listener "%s" given: must have a "%s" method',
                $listenerName,
                $event,
            ),
        );
    }

    public static function forInvalidListener(string $listenerName): self
    {
        return new self(
            sprintf(
                'Invalid event listener "%s" given, must be a dependency name, class name or an object',
                $listenerName,
            ),
        );
    }

    public static function forInvalidEventSubscriber(string $subscriberName): self
    {
        return new self(
            sprintf(
                'Invalid event subscriber "%s" given, must be a dependency name, class name or an instance implementing %s',
                $subscriberName,
                EventSubscriber::class,
            ),
        );
    }

    public static function forInvalidMigrationsCommand(string $command): self
    {
        return new self(
            sprintf(
                'Requested class "%s" is not a valid doctrine migrations command',
                $command,
            ),
        );
    }

    public static function forNonRegistryTypeProvider(TypeProvider $typeProvider): self
    {
        return new self(
            sprintf(
                'Cannot register the "doctrine.types" config: the connection configuration type provider must be an instance of %s, %s given',
                TypeRegistry::class,
                get_debug_type($typeProvider),
            ),
        );
    }
}
