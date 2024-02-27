<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DirectoryLoader;

use Kununu\DataFixtures\Adapter\ElasticsearchPhpArrayFixture;

abstract class ElasticsearchArrayDirectoryFixture extends ElasticsearchPhpArrayFixture
{
    use DirectoryFileSearchTrait;

    protected function getDirectory(): string
    {
        return 'Elasticsearch';
    }
}
