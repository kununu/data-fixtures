<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DirectoryLoader;

use Kununu\DataFixtures\Adapter\ElasticSearchJsonFileFixture;

abstract class ElasticSearchJsonDirectoryFixture extends ElasticSearchJsonFileFixture
{
    use DirectoryFileSearchTrait;

    protected function getDirectory(): string
    {
        return 'Elasticsearch';
    }
}
