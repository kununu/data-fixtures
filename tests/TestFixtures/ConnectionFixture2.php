<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;

final class ConnectionFixture2 implements ConnectionFixtureInterface
{
    public function load(Connection $connection): void
    {
    }
}
