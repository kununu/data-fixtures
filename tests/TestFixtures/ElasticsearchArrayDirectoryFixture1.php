<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\DirectoryLoader\ElasticsearchArrayDirectoryFixture;

final class ElasticsearchArrayDirectoryFixture1 extends ElasticsearchArrayDirectoryFixture
{
    protected function getDocumentIdForBulkIndexation(array $document): mixed
    {
        return $document['id'];
    }

    protected function getDocumentType(): string
    {
        return '_doc';
    }
}
