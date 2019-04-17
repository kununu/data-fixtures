<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;

final class ConnectionExecutor implements ExecutorInterface
{
    use ConnectionToolsTrait;

    private $connection;

    private $purger;

    public function __construct(Connection $connection, PurgerInterface $purger)
    {
        $this->connection = $connection;
        $this->purger = $purger;
    }

    public function execute(array $fixtures, $append = false) : void
    {
        $this->connection->beginTransaction();

        try {
            if ($append === false) {
                $this->purger->purge();
            }

            $this->connection->exec($this->getDisableForeignKeysChecksStatementByDriver($this->connection->getDriver()));

            foreach ($fixtures as $fixture) {
                $this->load($fixture);
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        } finally {
            $this->connection->exec($this->getEnableForeignKeysChecksStatementByDriver($this->connection->getDriver()));
        }
    }

    private function load(ConnectionFixtureInterface $fixture)
    {
        $fixture->load($this->connection);
    }
}
