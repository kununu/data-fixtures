<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Doctrine\DBAL\Connection;

final class ConnectionPurger implements TransactionalPurgerInterface
{
    private const PURGE_MODE_DELETE = 1;
    private const PURGE_MODE_TRUNCATE = 2;

    private $connection;

    private $tables;

    private $excludedTables;

    private $purgeMode = self::PURGE_MODE_DELETE;

    private $transactional = true;

    public function __construct(Connection $connection, array $excludedTables = [])
    {
        $this->connection = $connection;
        $this->tables = $connection->getSchemaManager()->listTableNames();
        $this->excludedTables = $excludedTables;
    }

    public function purge() : void
    {
        $tables = array_diff($this->tables, $this->excludedTables);

        if (!empty($tables)) {
            $platform = $this->connection->getDatabasePlatform();

            if ($this->transactional) {
                $this->connection->beginTransaction();
            }

            try {
                $this->connection->exec('SET FOREIGN_KEY_CHECKS=0');

                foreach ($tables as $tbl) {
                    if ($this->purgeMode === self::PURGE_MODE_DELETE) {
                        $this->connection->executeUpdate('DELETE FROM ' . $this->connection->quoteIdentifier($tbl));
                    } else {
                        $this->connection->executeUpdate($platform->getTruncateTableSQL($this->connection->quoteIdentifier($tbl), true));
                    }
                }

                if ($this->transactional) {
                    $this->connection->commit();
                }

                $this->connection->exec('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable $e) {
                if ($this->transactional) {
                    $this->connection->rollBack();
                }
                $this->connection->exec('SET FOREIGN_KEY_CHECKS=1');

                throw $e;
            }
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

    public function enableTransactional() : void
    {
        $this->transactional = true;
    }

    public function disableTransactional() : void
    {
        $this->transactional = false;
    }
}
