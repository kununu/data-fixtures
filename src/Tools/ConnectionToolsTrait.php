<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;

trait ConnectionToolsTrait
{
    protected function getDatabaseTables(Connection $connection): array
    {
        // This way we support both doctrine/dbal ^2.9 and ^3.1
        $method = method_exists($connection, 'createSchemaManager') ? 'createSchemaManager' : 'getSchemaManager';

        return $connection->$method()->listTableNames();
    }

    protected function executeQuery(Connection $connection, string $sql): int
    {
        // This way we support both doctrine/dbal ^2.9 and ^3.1
        $method = method_exists($connection, 'executeStatement') ? 'executeStatement' : 'exec';

        return $connection->$method($sql);
    }

    protected function getDisableForeignKeysChecksStatementByDriver(Driver $driver): string
    {
        $databasePlatform = $driver->getDatabasePlatform();

        // This way we support both doctrine/dbal ^2.9 and ^3.1
        return match (self::dbalSupportsAbstractMySQLPlatform()) {
            true => match (true) {
                $databasePlatform instanceof AbstractMySQLPlatform => 'SET FOREIGN_KEY_CHECKS=0',
                $databasePlatform instanceof SqlitePlatform        => 'PRAGMA foreign_keys = OFF',
                default                                            => '',
            },
            default => match (true) {
                $driver instanceof AbstractMySQLDriver  => 'SET FOREIGN_KEY_CHECKS=0',
                $driver instanceof AbstractSQLiteDriver => 'PRAGMA foreign_keys = OFF',
                default                                 => ''
            }
        };
    }

    protected function getEnableForeignKeysChecksStatementByDriver(Driver $driver): string
    {
        $databasePlatform = $driver->getDatabasePlatform();

        // This way we support both doctrine/dbal ^2.9 and ^3.1
        return match (self::dbalSupportsAbstractMySQLPlatform()) {
            true => match (true) {
                $databasePlatform instanceof AbstractMySQLPlatform => 'SET FOREIGN_KEY_CHECKS=1',
                $databasePlatform instanceof SqlitePlatform        => 'PRAGMA foreign_keys = ON',
                default                                            => '',
            },
            default => match (true) {
                $driver instanceof AbstractMySQLDriver  => 'SET FOREIGN_KEY_CHECKS=1',
                $driver instanceof AbstractSQLiteDriver => 'PRAGMA foreign_keys = ON',
                default                                 => ''
            }
        };
    }

    protected static function dbalSupportsAbstractMySQLPlatform(): bool
    {
        return class_exists('Doctrine\DBAL\Platforms\AbstractMySQLPlatform');
    }
}
