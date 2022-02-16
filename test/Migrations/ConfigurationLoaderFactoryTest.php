<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine\Migrations;

use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\Migrations\ConfigurationLoaderFactory;

use function assert;

final class ConfigurationLoaderFactoryTest extends TestCase
{
    private const DIRECTORY      = 'test/TestAsset';
    private const NS             = 'Acme\Lib\Migrations';
    private const TABLE          = 'migrations_performed';
    private const COLUMN         = 'version';
    private const ALL_OR_NOTHING = true;
    private const CHECK_PLATFORM = true;
    private const COLUMN_LENGTH  = 42;

    public function testExtendsAbstractFactory(): void
    {
        self::assertInstanceOf(AbstractFactory::class, new ConfigurationLoaderFactory());
    }

    public function testConfigValues(): void
    {
        $container = $this->createStub(ContainerInterface::class);

        $config = [
            'doctrine' => [
                'migrations' => [
                    'orm_default' => [
                        'table_storage' => [
                            'table_name' => self::TABLE,
                            'version_column_name' => self::COLUMN,
                            'version_column_length' => self::COLUMN_LENGTH,
                            'executed_at_column_name' => 'executed_at',
                            'execution_time_column_name' => 'execution_time',
                        ],
                        'migrations_paths' => [self::NS => self::DIRECTORY],
                        'all_or_nothing' => self::ALL_OR_NOTHING,
                        'check_database_platform' => self::CHECK_PLATFORM,
                    ],
                ],
            ],
        ];

        $container->method('has')
            ->willReturnMap(
                [
                    ['config', true],
                ]
            );

        $container->method('get')
            ->willReturnMap(
                [
                    ['config', $config],
                ]
            );

        $migrationsConfiguration = (new ConfigurationLoaderFactory())($container);
        self::assertInstanceOf(ConfigurationArray::class, $migrationsConfiguration);
        $configuration = $migrationsConfiguration->getConfiguration();

        self::assertSame(self::ALL_OR_NOTHING, $configuration->isAllOrNothing());
        self::assertSame(self::CHECK_PLATFORM, $configuration->isDatabasePlatformChecked());
        $storageConfiguration = $configuration->getMetadataStorageConfiguration();
        self::assertNotNull($storageConfiguration);
        assert($storageConfiguration instanceof TableMetadataStorageConfiguration);
        self::assertSame(self::TABLE, $storageConfiguration->getTableName());
        self::assertSame(self::COLUMN, $storageConfiguration->getVersionColumnName());
        self::assertSame(self::COLUMN_LENGTH, $storageConfiguration->getVersionColumnLength());

        self::assertSame([self::NS => self::DIRECTORY], $configuration->getMigrationDirectories());
    }
}
