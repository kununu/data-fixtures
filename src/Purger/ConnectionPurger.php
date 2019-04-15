<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Doctrine\DBAL\Connection;

final class ConnectionPurger implements PurgerInterface
{
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

        if (!empty($tables)) {
            $platform = $this->connection->getDatabasePlatform();

            foreach ($tables as $tbl) {
                if ($this->purgeMode === self::PURGE_MODE_DELETE) {
                    $this->connection->executeUpdate('DELETE FROM ' . $tbl);
                } else {
                    $this->connection->executeUpdate($platform->getTruncateTableSQL($tbl, true));
                }
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
}
