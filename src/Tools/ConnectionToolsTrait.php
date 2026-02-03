<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Kununu\DataFixtures\Tools\DoctrineDbal\Version;

trait ConnectionToolsTrait
{
    private const string MY_SQL_FK = 'SET FOREIGN_KEY_CHECKS = %d';
    private const string SQLITE_FK = 'PRAGMA foreign_keys = %s';

    protected function disableForeignKeysChecks(Connection $connection): void
    {
        $connection->executeStatement($this->getDisableForeignKeysChecksStatement($connection));
    }

    protected function enableForeignKeysChecks(Connection $connection): void
    {
        $connection->executeStatement($this->getEnableForeignKeysChecksStatement($connection));
    }

    protected function getDisableForeignKeysChecksStatement(Connection $connection): string
    {
        return $this->buildForeignKeysChecksStatement($connection, false);
    }

    protected function getEnableForeignKeysChecksStatement(Connection $connection): string
    {
        return $this->buildForeignKeysChecksStatement($connection, true);
    }

    private function buildForeignKeysChecksStatement(Connection $connection, bool $enable): string
    {
        return match (true) {
            $this->isMySQLPlatform($connection)  => sprintf(self::MY_SQL_FK, $enable ? 1 : 0),
            $this->isSQLitePlatform($connection) => sprintf(self::SQLITE_FK, $enable ? 'ON' : 'OFF'),
            default                              => '',
        };
    }

    private function isMySQLPlatform(Connection $connection): bool
    {
        return $this->isPlatform($connection, AbstractMySQLPlatform::class);
    }

    private function isSQLitePlatform(Connection $connection): bool
    {
        return $this->isPlatform($connection, Version::getSQLitePlatformClass());
    }

    private function isPlatform(Connection $connection, string $platformClass): bool
    {
        return is_a($connection->getDatabasePlatform(), $platformClass);
    }
}
