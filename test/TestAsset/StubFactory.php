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
    // phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
    public function retrieveConfig(ContainerInterface $container, string $configKey, string $section) : array
    {
        return parent::retrieveConfig($container, $configKey, $section);
    }

    // phpcs:enable

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig($configKey) : array
    {
        return [];
    }
}
