<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;

class StubFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey)
    {
        return $configKey;
    }

    // phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found

    /**
     * {@inheritDoc}
     */
    public function retrieveConfig(ContainerInterface $container, string $configKey, string $section): array
    {
        return parent::retrieveConfig($container, $configKey, $section);
    }

    // phpcs:enable

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [];
    }
}
