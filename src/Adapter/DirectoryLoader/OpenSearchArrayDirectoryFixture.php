<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DirectoryLoader;

use Kununu\DataFixtures\Adapter\OpenSearchPhpArrayFixture;

abstract class OpenSearchArrayDirectoryFixture extends OpenSearchPhpArrayFixture
{
    use DirectoryFileSearchTrait;

    protected function getDirectory(): string
    {
        return 'OpenSearch';
    }
}
