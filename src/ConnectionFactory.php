<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver as PdoMysqlDriver;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;
use Psr\Container\ContainerInterface;
use SensitiveParameter;

use function array_map;
use function array_merge;
use function is_string;

/** @extends AbstractFactory<Connection> */
final class ConnectionFactory extends AbstractFactory
{
    private static bool $areTypesRegistered = false;

    protected function createWithConfig(ContainerInterface $container, string $configKey): Connection
    {
        $this->registerTypes($container);

        $config = $this->retrieveConfig($container, $configKey, 'connection');
        $params = $this->parseDatabaseUrl($config['params'] + [
            'driverClass' => $config['driver_class'],
            'wrapperClass' => $config['wrapper_class'],
            'pdo' => is_string($config['pdo']) ? $container->get($config['pdo']) : $config['pdo'],
        ]);

        if (isset($params['primary'])) {
            $params['primary'] = $this->parseDatabaseUrl($params['primary']);
        }

        if (isset($params['replica'])) {
            $params['replica'] = array_map(
                fn (array $params): array => $this->parseDatabaseUrl($params),
                $params['replica'],
            );
        }

        $connection = DriverManager::getConnection(
            $params,
            $this->retrieveDependency(
                $container,
                $config['configuration'],
                'configuration',
                ConfigurationFactory::class,
            ),
        );
        $platform   = $connection->getDatabasePlatform();

        foreach ($config['doctrine_mapping_types'] as $dbType => $doctrineType) {
            $platform->registerDoctrineTypeMapping($dbType, $doctrineType);
        }

        return $connection;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig(string $configKey): array
    {
        return [
            'driver_class' => PdoMysqlDriver::class,
            'wrapper_class' => null,
            'pdo' => null,
            'configuration' => $configKey,
            'params' => [],
            'doctrine_mapping_types' => [],
        ];
    }

    /**
     * Registers all declared typed, if not already done.
     */
    private function registerTypes(ContainerInterface $container): void
    {
        if (self::$areTypesRegistered) {
            return;
        }

        $applicationConfig        = $container->has('config') ? $container->get('config') : [];
        $typesConfig              = $applicationConfig['doctrine']['types'] ?? [];
        self::$areTypesRegistered = true;

        foreach ($typesConfig as $name => $className) {
            if (Type::hasType($name)) {
                Type::overrideType($name, $className);
                continue;
            }

            Type::addType($name, $className);
        }
    }

    /**
     * @param array<array-key, mixed> $params
     *
     * @return array<array-key, mixed>
     */
    private function parseDatabaseUrl(
        #[SensitiveParameter]
        array $params,
    ): array {
        if (! isset($params['url'])) {
            return $params;
        }

        $parser = new DsnParser();

        $parsedParams = $parser->parse($params['url']);
        unset($params['url']);

        $result = array_merge($params, $parsedParams);

        if (isset($result['driver']) && isset($result['driverClass'])) {
            unset($result['driverClass']);
        }

        return $result;
    }
}
