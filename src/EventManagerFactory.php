<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\DomainException;
use Roave\PsrContainerDoctrine\Exception\InvalidArgumentException;

use function class_exists;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;

/** @method EventManager __invoke(ContainerInterface $container) */
final class EventManagerFactory extends AbstractFactory
{
    protected function createWithConfig(ContainerInterface $container, string $configKey): EventManager
    {
        $config       = $this->retrieveConfig($container, $configKey, 'event_manager');
        $eventManager = new EventManager();

        foreach ($config['subscribers'] as $subscriber) {
            if (is_object($subscriber)) {
                $subscriberName = $subscriber::class;
            } elseif (! is_string($subscriber)) {
                $subscriberName = gettype($subscriber);
            } elseif ($container->has($subscriber)) {
                $subscriber     = $container->get($subscriber);
                $subscriberName = $subscriber;
            } elseif (class_exists($subscriber)) {
                $subscriber     = new $subscriber();
                $subscriberName = $subscriber::class;
            } else {
                $subscriberName = $subscriber;
            }

            if (! $subscriber instanceof EventSubscriber) {
                throw DomainException::forInvalidEventSubscriber($subscriberName);
            }

            $eventManager->addEventSubscriber($subscriber);
        }

        foreach ($config['listeners'] as $listenerConfig) {
            if (! is_array($listenerConfig)) {
                throw InvalidArgumentException::forInvalidEventListenerConfig($listenerConfig);
            }

            $listener     = $listenerConfig['listener'];
            $listenerName = $listener;

            if (is_object($listener)) {
                $listenerName = $listener::class;
            } elseif (! is_string($listener)) {
                $listenerName = gettype($listener);
            } elseif ($container->has($listener)) {
                $listener = $container->get($listener);
            } elseif (class_exists($listener)) {
                $listener     = new $listener();
                $listenerName = $listener::class;
            }

            if (! is_object($listener)) {
                throw DomainException::forInvalidListener($listenerName);
            }

            foreach ((array) $listenerConfig['events'] as $event) {
                if (! method_exists($listener, $event)) {
                    throw DomainException::forMissingMethodOnListener($listenerName, $event);
                }
            }

            $eventManager->addEventListener($listenerConfig['events'], $listener);
        }

        return $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [
            'subscribers' => [],
            'listeners' => [],
        ];
    }
}
