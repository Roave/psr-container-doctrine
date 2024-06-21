<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileDriver;

/** @extends FileDriver<null> */
final class StubFileDriver extends FileDriver
{
    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile($file): array
    {
        return [];
    }

    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint

    /** @param string|class-string $className */
    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
    }

    // phpcs:enable
}
