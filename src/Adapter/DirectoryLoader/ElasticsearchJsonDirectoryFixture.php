<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DirectoryLoader;

use Kununu\DataFixtures\Adapter\ElasticsearchJsonFileFixture;

abstract class ElasticsearchJsonDirectoryFixture extends ElasticsearchJsonFileFixture
{
    use DirectoryFileSearchTrait;

    protected function getDirectory(): string
    {
        return 'Elasticsearch';
    }
}
