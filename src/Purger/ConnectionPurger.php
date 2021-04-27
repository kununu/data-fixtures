<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;

final class ConnectionPurger implements PurgerInterface
{
    use ConnectionToolsTrait;

    private const PURGE_MODE_DELETE = 1;
    private const PURGE_MODE_TRUNCATE = 2;

    private $connection;

    private $tables;

    private $excludedTables;

    private $purgeMode = self::PURGE_MODE_DELETE;

    public function __construct(Connection $connection, array $excludedTables = [])
    {
        $this->connection = $connection;
        $this->tables = $connection->createSchemaManager()->listTableNames();
        $this->excludedTables = $excludedTables;
    }

    public function purge(): void
    {
        $tables = array_diff($this->tables, $this->excludedTables);

        if (empty($tables)) {
            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        $this->connection->beginTransaction();

        try {
            $this->connection->executeStatement($this->getDisableForeignKeysChecksStatementByDriver($this->connection->getDriver()));

            foreach ($tables as $tableName) {
                $this->purgeTable($platform, $tableName);
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        } finally {
            $this->connection->executeStatement($this->getEnableForeignKeysChecksStatementByDriver($this->connection->getDriver()));
        }
    }

    public function setPurgeMode(int $mode): void
    {
        if (!in_array($mode, [self::PURGE_MODE_DELETE, self::PURGE_MODE_TRUNCATE])) {
            throw new \Exception(sprintf('Purge Mode "%d" is not valid', $mode));
        }

        $this->purgeMode = $mode;
    }

    public function getPurgeMode(): int
    {
        return $this->purgeMode;
    }

    private function purgeTable(AbstractPlatform $platform, string $tableName): void
    {
        if ($this->purgeMode === self::PURGE_MODE_DELETE) {
            $this->connection->executeStatement('DELETE FROM ' . $this->connection->quoteIdentifier($tableName));
        } else {
            $this->connection->executeStatement($platform->getTruncateTableSQL($this->connection->quoteIdentifier($tableName), true));
        }
    }
}
