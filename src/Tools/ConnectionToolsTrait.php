<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

trait ConnectionToolsTrait
{
    protected function getDatabaseTables(Connection $connection): array
    {
        // This way we support both doctrine/dbal ^2.9 and ^3.1
        if (method_exists($connection, 'createSchemaManager')) {
            return $connection->createSchemaManager()->listTableNames();
        }

        return $connection->getSchemaManager()->listTableNames();
    }

    protected function executeQuery(Connection $connection, string $sql): int
    {
        // This way we support both doctrine/dbal ^2.9 and ^3.1
        if (method_exists($connection, 'executeStatement')) {
            return $connection->executeStatement($sql);
        }

        return $connection->exec($sql);
    }

    protected function getDisableForeignKeysChecksStatementByDriver(Driver $driver): string
    {
        if ($driver instanceof Driver\AbstractMySQLDriver) {
            return 'SET FOREIGN_KEY_CHECKS=0';
        }

        if ($driver instanceof Driver\AbstractSQLiteDriver) {
            return 'PRAGMA foreign_keys = OFF';
        }

        return '';
    }

    protected function getEnableForeignKeysChecksStatementByDriver(Driver $driver): string
    {
        if ($driver instanceof Driver\AbstractMySQLDriver) {
            return 'SET FOREIGN_KEY_CHECKS=1';
        }

        if ($driver instanceof Driver\AbstractSQLiteDriver) {
            return 'PRAGMA foreign_keys = ON';
        }

        return '';
    }
}
