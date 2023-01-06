<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\DirectoryLoader\ElasticSearchArrayDirectoryFixture;

final class ElasticSearchArrayDirectoryFixture1 extends ElasticSearchArrayDirectoryFixture
{
    protected function getDocumentIdForBulkIndexation(array $document)
    {
        return $document['id'];
    }

    protected function getDocumentType(): ?string
    {
        return '_doc';
    }
}
