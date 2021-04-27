<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\FixtureInterface;

interface ConnectionFixtureInterface extends FixtureInterface
{
    public function load(Connection $connection): void;
}
