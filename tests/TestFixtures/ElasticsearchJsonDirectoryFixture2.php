<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\DirectoryLoader\ElasticsearchJsonDirectoryFixture;

final class ElasticsearchJsonDirectoryFixture2 extends ElasticsearchJsonDirectoryFixture
{
    protected function getDocumentIdForBulkIndexation(array $document): mixed
    {
        return $document['uuid'];
    }
}
