<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

final readonly class StubEventListener
{
    public function onFlush(): void
    {
    }
}
