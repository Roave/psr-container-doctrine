<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\Repository;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Repository\ContainerRepositoryFactory;
use RuntimeException;
use stdClass;

class ContainerRepositoryFactoryTest extends TestCase
{
    public function testGetRepositoryReturnsService(): void
    {
        $fooEntity = 'Foo\FooEntity';
        $em        = $this->buildEntityManager([$fooEntity => 'my_repo']);
        $repo      = new EntityRepository($em, $em->getClassMetadata($fooEntity));
        $container = $this->buildContainer(['my_repo' => $repo]);
        $factory   = new ContainerRepositoryFactory($container);

        $this->assertSame($repo, $factory->getRepository($em, $fooEntity));
    }

    public function testServiceRepositoriesMustExtendObjectRepository(): void
    {
        $fooEntity = 'Foo\FooEntity';
        $em        = $this->buildEntityManager([$fooEntity => 'my_repo']);
        $repo      = new stdClass();
        $container = $this->buildContainer(['my_repo' => $repo]);
        $factory   = new ContainerRepositoryFactory($container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The service "my_repo" must implement ObjectRepository.');
        $factory->getRepository($em, $fooEntity);
    }

    public function testCustomRepositoryIsNotAValidClass(): void
    {
        $fooEntity = 'Foo\FooEntity';
        $em        = $this->buildEntityManager([$fooEntity => 'not_a_real_class']);
        $container = $this->buildContainer([]);
        $factory   = new ContainerRepositoryFactory($container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The "Foo\FooEntity" entity has a repositoryClass set to "not_a_real_class", ' .
            'but this is not a valid class. Check your class naming. '
        );
        $factory->getRepository($em, $fooEntity);
    }

    public function testGetRepositoryReturnsEntityRepository(): void
    {
        $fooEntity = 'Foo\FooEntity';
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with($this->equalTo($fooEntity))->willReturn(false);

        $em = $this->buildEntityManager([$fooEntity => null]);

        $repositoryFactory = new ContainerRepositoryFactory($container);

        $repository = $repositoryFactory->getRepository($em, $fooEntity);

        $this->assertInstanceOf(EntityRepository::class, $repository);
        $this->assertEquals($fooEntity, $repository->getClassName());

        // test instance
        $this->assertEquals($repository, $repositoryFactory->getRepository($em, $fooEntity));
    }

    /**
     * @param array<string, string|null> $entityRepositoryClasses
     */
    private function buildEntityManager(array $entityRepositoryClasses): EntityManagerInterface
    {
        $classMetadatas = [];
        foreach ($entityRepositoryClasses as $entityClass => $entityRepositoryClass) {
            $metadata = new ClassMetadata($entityClass);

            /** @psalm-suppress PropertyTypeCoercion */
            $metadata->customRepositoryClassName = $entityRepositoryClass;

            $classMetadatas[$entityClass] = $metadata;
        }

        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->expects($this->any())
            ->method('getClassMetadata')
            ->willReturnCallback(static function (string $class) use ($classMetadatas): ClassMetadata {
                return $classMetadatas[$class];
            });

        $em->expects($this->any())
            ->method('getConfiguration')
            ->willReturn(new Configuration());

        return $em;
    }

    /**
     * @param array<string, mixed> $services
     */
    private function buildContainer(array $services): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->any())
            ->method('has')
            ->willReturnCallback(static function (string $id) use ($services): bool {
                return isset($services[$id]);
            });
        $container->expects($this->any())
            ->method('get')
            ->willReturnCallback(static function (string $id) use ($services): object {
                return $services[$id];
            });

        return $container;
    }
}
