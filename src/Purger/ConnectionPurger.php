<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;
use Throwable;

final readonly class ConnectionPurger implements PurgerInterface
{
    use ConnectionToolsTrait;

    private array $tables;

    public function __construct(
        private Connection $connection,
        private array $excludedTables = [],
        private bool $transactional = true,
        private PurgeMode $purgeMode = PurgeMode::Delete,
    ) {
        $this->tables = $this->connection->createSchemaManager()->listTableNames();
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
            $this->disableForeignKeysChecks($this->connection);

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
            $this->enableForeignKeysChecks($this->connection);
        }
    }

    private function purgeTable(AbstractPlatform $platform, string $tableName): void
    {
        $query = PurgeMode::Delete->equals($this->purgeMode)
            ? sprintf('DELETE FROM %s', $this->connection->quoteIdentifier($tableName))
            : $platform->getTruncateTableSQL($tableName, true);

        $this->connection->executeStatement($query);
    }
}
