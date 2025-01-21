<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\DirectoryLoader\OpenSearchArrayDirectoryFixture;

final class OpenSearchArrayDirectoryFixture1 extends OpenSearchArrayDirectoryFixture
{
    protected function getDocumentIdForBulkIndexation(array $document): mixed
    {
        return $document['id'];
    }
}
