<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Purger\PurgerInterface;

final class NonTransactionalConnectionExecutor implements ExecutorInterface
{
    private readonly ExecutorInterface $executor;

    public function __construct(Connection $connection, PurgerInterface $purger)
    {
        $this->executor = new ConnectionExecutor($connection, $purger, false);
    }

    public function execute(array $fixtures, bool $append = false): void
    {
        $this->executor->execute($fixtures, $append);
    }
}
