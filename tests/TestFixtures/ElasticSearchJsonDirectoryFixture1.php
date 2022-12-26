<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\DirectoryLoader\ElasticSearchJsonDirectoryFixture;

final class ElasticSearchJsonDirectoryFixture1 extends ElasticSearchJsonDirectoryFixture
{
    protected function getDocumentIdForBulkIndexation(array $document)
    {
        return $document['uuid'];
    }
}
