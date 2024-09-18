<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;

abstract class ConnectionSqlFixture extends AbstractFileLoaderFixture implements ConnectionFixtureInterface
{
    use ConnectionToolsTrait;

    final public function load(Connection $connection): void
    {
        parent::loadFiles(fn(?string $sql) => match (true) {
            is_string($sql) => $connection->executeStatement($sql),
            default         => null,
        });
    }

    protected function getFileExtension(): string
    {
        return 'sql';
    }

    protected function getLoadMode(): LoadMode
    {
        return LoadMode::Load;
    }
}
