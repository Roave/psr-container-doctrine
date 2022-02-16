<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\ORM;

use Doctrine\ORM\Mapping\EntityListenerResolver;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\RuntimeException;

class ContainerEntityListenerResolver implements EntityListenerResolver
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($className): object
    {
        return $this->container->get($className);
    }

    /**
     * {@inheritdoc}
     */
    public function clear($className = null): void
    {
        throw new RuntimeException('Use container features instead.');
    }

    /**
     * {@inheritdoc}
     */
    public function register($object): void
    {
        throw new RuntimeException('Use container features instead.');
    }
}
