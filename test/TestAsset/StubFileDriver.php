<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\ClassMetadata;

class StubFileDriver extends FileDriver
{
    // Disable these sniffs, since we can't control inheritance
    // phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
    protected function loadMappingFile($file)
    {
        return [];
    }

    public function loadMetadataForClass($className, ClassMetadata $metadata) : void
    {
    }

    // phpcs:enable
}
