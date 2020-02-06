<?php
declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;

class StubFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    protected function createWithConfig(ContainerInterface $container, $configKey)
    {
        return $configKey;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveConfig(ContainerInterface $container, $configKey, $section)
    {
        return parent::retrieveConfig($container, $configKey, $section);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig($configKey)
    {
        return [];
    }
}
