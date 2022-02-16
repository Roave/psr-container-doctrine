<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function class_exists;
use function spl_object_hash;
use function sprintf;

final class ContainerRepositoryFactory implements RepositoryFactory
{
    /** @var ObjectRepository[] */
    private array $managedRepositories = [];

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $metadata             = $entityManager->getClassMetadata($entityName);
        $customRepositoryName = $metadata->customRepositoryClassName;

        if ($customRepositoryName !== null) {
            // fetch from the container
            if ($this->container->has($customRepositoryName)) {
                $repository = $this->container->get($customRepositoryName);

                if (! $repository instanceof ObjectRepository) {
                    throw new RuntimeException(sprintf(
                        'The service "%s" must implement ObjectRepository.',
                        $customRepositoryName
                    ));
                }

                return $repository;
            }

            if (! class_exists($customRepositoryName)) {
                throw new RuntimeException(sprintf(
                    'The "%s" entity has a repositoryClass set to "%s", ' .
                    'but this is not a valid class. Check your class naming. ',
                    $metadata->name,
                    $customRepositoryName,
                ));
            }

            // allow the repository to be created below
        }

        return $this->getOrCreateRepository($entityManager, $metadata);
    }

    private function getOrCreateRepository(
        EntityManagerInterface $entityManager,
        ClassMetadata $metadata
    ): ObjectRepository {
        $repositoryHash = $metadata->getName() . spl_object_hash($entityManager);
        if (isset($this->managedRepositories[$repositoryHash])) {
            return $this->managedRepositories[$repositoryHash];
        }

        /**
         * @var  class-string<ObjectRepository> $repositoryClassName
         * @psalm-suppress NoInterfaceProperties
         */
        $repositoryClassName = $metadata->customRepositoryClassName
            ?? $entityManager->getConfiguration()->getDefaultRepositoryClassName();

        return $this->managedRepositories[$repositoryHash] = new $repositoryClassName($entityManager, $metadata);
    }
}
