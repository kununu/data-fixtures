<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\DirectoryLoader\OpenSearchJsonDirectoryFixture;

final class OpenSearchJsonDirectoryFixture2 extends OpenSearchJsonDirectoryFixture
{
    protected function getDocumentIdForBulkIndexation(array $document): mixed
    {
        return $document['uuid'];
    }
}
