<?php
declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Doctrine\Common\EventSubscriber;

class StubEventSubscriber implements EventSubscriber
{
    /**
     * {q@nheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['foo'];
    }
}
