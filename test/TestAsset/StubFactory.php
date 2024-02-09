<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\TestAsset;

use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;

/** @extends AbstractFactory<string> */
final class StubFactory extends AbstractFactory
{
    protected function createWithConfig(ContainerInterface $container, string $configKey): string
    {
        return $configKey;
    }

    /**
     * {@inheritDoc}
     */
    public function publicRetrieveConfig(ContainerInterface $container, string $configKey, string $section): array
    {
        return parent::retrieveConfig($container, $configKey, $section);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [];
    }
}
