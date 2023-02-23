<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Kununu\DataFixtures\Exception\InvalidConnectionPurgeModeException;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;
use Throwable;

final class ConnectionPurger implements PurgerInterface
{
    use ConnectionToolsTrait;

    private const PURGE_MODE_DELETE = 1;
    private const PURGE_MODE_TRUNCATE = 2;

    private array $tables;
    private int $purgeMode = self::PURGE_MODE_DELETE;

    public function __construct(
        private Connection $connection,
        private array $excludedTables = [],
        private bool $transactional = true
    ) {
        $this->tables = $this->getDatabaseTables($connection);
    }

    public function purge(): void
    {
        $tablesToPurge = array_diff($this->tables, $this->excludedTables);

        if (empty($tablesToPurge)) {
            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        if ($this->transactional) {
            $this->connection->beginTransaction();
        }

        try {
            $this->executeQuery(
                $this->connection,
                $this->getDisableForeignKeysChecksStatementByDriver($this->connection->getDriver())
            );

            foreach ($tablesToPurge as $tableName) {
                $this->purgeTable($platform, $tableName);
            }

            if ($this->transactional) {
                $this->connection->commit();
            }
        } catch (Throwable $e) {
            if ($this->transactional) {
                $this->connection->rollBack();
            }
            throw $e;
        } finally {
            $this->executeQuery(
                $this->connection,
                $this->getEnableForeignKeysChecksStatementByDriver($this->connection->getDriver())
            );
        }
    }

    public function setPurgeMode(int $mode): void
    {
        if (!in_array($mode, [self::PURGE_MODE_DELETE, self::PURGE_MODE_TRUNCATE])) {
            throw new InvalidConnectionPurgeModeException(sprintf('Purge Mode "%d" is not valid', $mode));
        }

        $this->purgeMode = $mode;
    }

    public function getPurgeMode(): int
    {
        return $this->purgeMode;
    }

    private function purgeTable(AbstractPlatform $platform, string $tableName): void
    {
        $query = $this->purgeMode === self::PURGE_MODE_DELETE
            ? sprintf('DELETE FROM %s', $this->connection->quoteIdentifier($tableName))
            : $platform->getTruncateTableSQL($tableName, true);

        $this->executeQuery($this->connection, $query);
    }
}
