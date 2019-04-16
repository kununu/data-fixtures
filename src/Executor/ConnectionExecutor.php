<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Purger\TransactionalPurgerInterface;

final class ConnectionExecutor implements ExecutorInterface
{
    private $connection;

    private $purger;

    public function __construct(Connection $connection, TransactionalPurgerInterface $purger)
    {
        $this->connection = $connection;
        $this->purger = $purger;
    }

    public function execute(array $fixtures, $append = false) : void
    {
        $this->connection->beginTransaction();

        try {
            if ($append === false) {
                $this->purger->disableTransactional();
                $this->purger->purge();
                $this->purger->enableTransactional();
            }

            $this->connection->exec('SET FOREIGN_KEY_CHECKS=0');

            foreach ($fixtures as $fixture) {
                $this->load($fixture);
            }

            $this->connection->commit();
            $this->connection->exec('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Throwable $e) {
            $this->purger->enableTransactional();
            $this->connection->rollBack();
            $this->connection->exec('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }

    private function load(ConnectionFixtureInterface $fixture)
    {
        $fixture->load($this->connection);
    }
}
