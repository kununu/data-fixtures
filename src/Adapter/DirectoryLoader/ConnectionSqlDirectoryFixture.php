<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DirectoryLoader;

use Kununu\DataFixtures\Adapter\ConnectionSqlFixture;

abstract class ConnectionSqlDirectoryFixture extends ConnectionSqlFixture
{
    use DirectoryFileSearchTrait;

    protected function getDirectory(): string
    {
        return 'Sql';
    }
}
