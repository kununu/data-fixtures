<?php declare(strict_types=1);

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
        $this->tables = $connection->getSchemaManager()->listTableNames();
        $this->excludedTables = $excludedTables;
    }

    public function purge() : void
    {
        $tables = array_diff($this->tables, $this->excludedTables);

        if (empty($tables)) {
            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        $this->connection->beginTransaction();

        try {
            $this->connection->exec($this->getDisableForeignKeysChecksStatementByDriver($this->connection->getDriver()));

            foreach ($tables as $tableName) {
                $this->purgeTable($platform, $tableName);
            }

            $this->connection->commit();

            $this->connection->exec($this->getEnableForeignKeysChecksStatementByDriver($this->connection->getDriver()));
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            $this->connection->exec($this->getEnableForeignKeysChecksStatementByDriver($this->connection->getDriver()));

            throw $e;
        }
    }

    public function setPurgeMode(int $mode) : void
    {
        if (!in_array($mode, [self::PURGE_MODE_DELETE, self::PURGE_MODE_TRUNCATE])) {
            throw new \Exception(
                sprintf('Purge Mode "%d" is not valid', $mode)
            );
        }

        $this->purgeMode = $mode;
    }

    public function getPurgeMode() : int
    {
        return $this->purgeMode;
    }

    private function purgeTable(AbstractPlatform $platform, string $tableName) : void
    {
        if ($this->purgeMode === self::PURGE_MODE_DELETE) {
            $this->connection->executeUpdate('DELETE FROM ' . $this->connection->quoteIdentifier($tableName));
        } else {
            $this->connection->executeUpdate($platform->getTruncateTableSQL($this->connection->quoteIdentifier($tableName), true));
        }
    }
}
