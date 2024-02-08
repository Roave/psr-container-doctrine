<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/** @extends AbstractFactory<EntityManager> */
final class EntityManagerFactory extends AbstractFactory
{
    protected function createWithConfig(ContainerInterface $container, string $configKey): EntityManager
    {
        $config = $this->retrieveConfig($container, $configKey, 'entity_manager');

        return new EntityManager(
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
            $this->retrieveDependency(
                $container,
                $config['event_manager'],
                'event_manager',
                EventManagerFactory::class,
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [
            'connection' => $configKey,
            'configuration' => $configKey,
            'event_manager' => $configKey,
        ];
    }
}
