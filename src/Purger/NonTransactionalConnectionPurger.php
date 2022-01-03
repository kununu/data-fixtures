<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Doctrine\DBAL\Connection;

final class NonTransactionalConnectionPurger implements PurgerInterface
{
    private $purger;

    public function __construct(Connection $connection, array $excludedTables = [])
    {
        $this->purger = new ConnectionPurger($connection, $excludedTables, false);
    }

    public function purge(): void
    {
        $this->purger->purge();
    }
}
