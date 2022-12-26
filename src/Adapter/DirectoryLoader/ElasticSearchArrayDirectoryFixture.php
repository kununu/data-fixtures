<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DirectoryLoader;

use Kununu\DataFixtures\Adapter\ElasticSearchPhpArrayFixture;

abstract class ElasticSearchArrayDirectoryFixture extends ElasticSearchPhpArrayFixture
{
    use DirectoryFileSearchTrait;

    protected function getDirectory(): string
    {
        return 'Elasticsearch';
    }
}
