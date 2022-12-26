<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\ConnectionSqlFixture;

final class ConnectionSqlFixture1 extends ConnectionSqlFixture
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . '/Sql/ConnectionSqlDirectoryFixture1/fixture1.sql',
            __DIR__ . '/Sql/ConnectionSqlDirectoryFixture1/fixture2.sql',
            __DIR__ . '/Sql/ConnectionSqlDirectoryFixture1/fixture3.nonSql',
            __DIR__ . '/Sql/ConnectionSqlDirectoryFixture1/fixture4.sql',
        ];
    }
}
