<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileDriver;

class StubFileDriver extends FileDriver
{
    /**
     * {@inheritdoc}
     */
    protected function loadMappingFile($file): array
    {
        return [];
    }

    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint

    /**
     * @param string|class-string $className
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
    }

    // phpcs:enable
}
