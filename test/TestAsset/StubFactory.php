<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;

class StubFactory extends AbstractFactory
{
    protected function createWithConfig(ContainerInterface $container, string $configKey): string
    {
        return $configKey;
    }

    // phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found

    /**
     * {@inheritdoc}
     */
    public function retrieveConfig(ContainerInterface $container, string $configKey, string $section): array
    {
        return parent::retrieveConfig($container, $configKey, $section);
    }

    // phpcs:enable

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [];
    }
}
