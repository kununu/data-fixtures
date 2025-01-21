<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticsearchFixture;

final class ElasticsearchFixture3 extends ElasticsearchFixture
{
    public const array DOCUMENTS = [
        [
            'id'         => 1,
            'name'       => 'Document 1',
            'attributes' => [
                'attrib_1' => 1,
                'attrib_2' => 'active',
                'attrib_3' => true,
            ],
        ],
        [
            'id'         => 2,
            'name'       => 'Document 2',
            'attributes' => [
                'attrib_1' => 2,
                'attrib_2' => 'inactive',
                'attrib_3' => false,
            ],
        ],
    ];

    public function load(Client $elasticSearch, string $indexName, bool $throwOnFail = true): void
    {
        $elasticSearch->bulk([
            'type' => '_doc',
            'body' => $this->prepareBodyForBulkIndexation($indexName, self::DOCUMENTS),
        ]);
    }

    protected function getDocumentIdForBulkIndexation(array $document): mixed
    {
        return $document['id'];
    }
}
