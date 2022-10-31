<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/** @method EntityManager __invoke(ContainerInterface $container) */
final class EntityManagerFactory extends AbstractFactory
{
    protected function createWithConfig(ContainerInterface $container, string $configKey): EntityManager
    {
        $config = $this->retrieveConfig($container, $configKey, 'entity_manager');

        return EntityManager::create(
            $this->retrieveDependency(
                $container,
                $config['connection'],
                'connection',
                ConnectionFactory::class,
            ),
            $this->retrieveDependency(
                $container,
                $config['configuration'],
                'configuration',
                ConfigurationFactory::class,
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [
            'connection' => $configKey,
            'configuration' => $configKey,
        ];
    }
}
