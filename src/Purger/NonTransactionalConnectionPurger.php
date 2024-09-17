<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Doctrine\DBAL\Connection;

final readonly class NonTransactionalConnectionPurger implements PurgerInterface
{
    private PurgerInterface $purger;

    public function __construct(Connection $connection, array $excludedTables = [])
    {
        $this->purger = new ConnectionPurger($connection, $excludedTables, false);
    }

    public function purge(): void
    {
        $this->purger->purge();
    }
}
