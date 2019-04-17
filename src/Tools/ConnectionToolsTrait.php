<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Doctrine\DBAL\Driver;

trait ConnectionToolsTrait
{
    protected function getDisableForeignKeysChecksStatementByDriver(Driver $driver) : string
    {
        if ($driver instanceof Driver\AbstractMySQLDriver) {
            return 'SET FOREIGN_KEY_CHECKS=0';
        }

        if ($driver instanceof Driver\AbstractSQLiteDriver) {
            return 'PRAGMA foreign_keys = OFF';
        }

        return '';
    }

    protected function getEnableForeignKeysChecksStatementByDriver(Driver $driver) : string
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
