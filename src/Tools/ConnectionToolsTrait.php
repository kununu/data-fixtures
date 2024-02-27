<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;

trait ConnectionToolsTrait
{
    protected function getDisableForeignKeysChecksStatementByDriver(Driver $driver): string
    {
        $databasePlatform = $driver->getDatabasePlatform();

        return match (true) {
            $driver instanceof AbstractMySQLDriver,
            $databasePlatform instanceof AbstractMySQLPlatform => 'SET FOREIGN_KEY_CHECKS=0',

            $driver instanceof AbstractSQLiteDriver,
            $databasePlatform instanceof SqlitePlatform        => 'PRAGMA foreign_keys = OFF',
            default                                            => '',
        };
    }

    protected function getEnableForeignKeysChecksStatementByDriver(Driver $driver): string
    {
        $databasePlatform = $driver->getDatabasePlatform();

        return match (true) {
            $driver instanceof AbstractMySQLDriver,
            $databasePlatform instanceof AbstractMySQLPlatform => 'SET FOREIGN_KEY_CHECKS=1',

            $driver instanceof AbstractSQLiteDriver,
            $databasePlatform instanceof SqlitePlatform        => 'PRAGMA foreign_keys = ON',
            default                                            => '',
        };
    }
}
