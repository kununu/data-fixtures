<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Kununu\DataFixtures\Adapter\ConnectionSqlFixture;

final class InvalidConnectionSqlFixture extends ConnectionSqlFixture
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . '/fixture1.sql',
        ];
    }
}
