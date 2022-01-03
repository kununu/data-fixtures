<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;
use Throwable;

final class ConnectionExecutor implements ExecutorInterface
{
    use ConnectionToolsTrait;

    private $connection;
    private $purger;
    private $transactional;

    public function __construct(Connection $connection, PurgerInterface $purger, bool $transactional = true)
    {
        $this->connection = $connection;
        $this->purger = $purger;
        $this->transactional = $transactional;
    }

    public function execute(array $fixtures, $append = false): void
    {
        if ($this->transactional) {
            $this->connection->beginTransaction();
        }

        try {
            if ($append === false) {
                $this->purger->purge();
            }

            $this->executeQuery($this->connection, $this->getDisableForeignKeysChecksStatementByDriver($this->connection->getDriver()));

            foreach ($fixtures as $fixture) {
                $this->load($fixture);
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
            $this->executeQuery($this->connection, $this->getEnableForeignKeysChecksStatementByDriver($this->connection->getDriver()));
        }
    }

    private function load(ConnectionFixtureInterface $fixture): void
    {
        $fixture->load($this->connection);
    }
}
