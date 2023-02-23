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

    public function __construct(
        private Connection $connection,
        private PurgerInterface $purger,
        private bool $transactional = true
    ) {
    }

    public function execute(array $fixtures, bool $append = false): void
    {
        if ($this->transactional) {
            $this->connection->beginTransaction();
        }

        try {
            if ($append === false) {
                $this->purger->purge();
            }

            $this->executeQuery(
                $this->connection,
                $this->getDisableForeignKeysChecksStatementByDriver($this->connection->getDriver())
            );

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
            $this->executeQuery(
                $this->connection,
                $this->getEnableForeignKeysChecksStatementByDriver($this->connection->getDriver())
            );
        }
    }

    private function load(ConnectionFixtureInterface $fixture): void
    {
        $fixture->load($this->connection);
    }
}
