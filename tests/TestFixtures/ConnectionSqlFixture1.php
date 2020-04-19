<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\ConnectionSqlFixture;

final class ConnectionSqlFixture1 extends ConnectionSqlFixture
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . '/Sql/fixture1.sql',
            __DIR__ . '/Sql/fixture2.sql',
            __DIR__ . '/Sql/fixture3.nonSql',
            __DIR__ . '/Sql/fixture4.sql',
        ];
    }
}
