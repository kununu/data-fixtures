<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;

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

    protected function getDisableForeignKeysChecksStatementByDriver(Driver $driver): string
    {
        if ($driver instanceof AbstractMySQLDriver) {
            return 'SET FOREIGN_KEY_CHECKS=0';
        }

        if ($driver instanceof AbstractSQLiteDriver) {
            return 'PRAGMA foreign_keys = OFF';
        }

        return '';
    }

    protected function getEnableForeignKeysChecksStatementByDriver(Driver $driver): string
    {
        if ($driver instanceof AbstractMySQLDriver) {
            return 'SET FOREIGN_KEY_CHECKS=1';
        }

        if ($driver instanceof AbstractSQLiteDriver) {
            return 'PRAGMA foreign_keys = ON';
        }

        return '';
    }
}
