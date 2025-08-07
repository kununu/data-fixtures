<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DirectoryLoader;

use Kununu\DataFixtures\Adapter\OpenSearchJsonFileFixture;

abstract class OpenSearchJsonDirectoryFixture extends OpenSearchJsonFileFixture
{
    use DirectoryFileSearchTrait;

    protected function getDirectory(): string
    {
        return 'OpenSearch';
    }
}
