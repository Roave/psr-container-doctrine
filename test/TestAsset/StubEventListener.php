<?php
/**
 * @license See the file LICENSE for copying permission
 */

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Doctrine\ORM\Event\OnFlushEventArgs;

class StubEventListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
    }
}
