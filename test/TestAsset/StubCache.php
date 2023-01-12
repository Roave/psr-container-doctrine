<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Doctrine\Common\Cache\Cache;

class StubCache implements Cache
{
    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return null;
    }
}
