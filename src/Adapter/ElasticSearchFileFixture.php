<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Elasticsearch\Client;

abstract class ElasticSearchFileFixture extends AbstractFileLoaderFixture implements ElasticSearchFixtureInterface
{
    use ElasticSearchFixtureTrait;

    public function load(Client $elasticSearch, string $indexName): void
    {
        parent::loadFiles(
            fn (array $documents) => match (true) {
                empty($documents) => null,
                default           => $elasticSearch->bulk(
                    array_merge(
                        ['body' => $this->prepareBodyForBulkIndexation($indexName, $documents)],
                        is_string($documentType = $this->getDocumentType()) ? ['type' => $documentType] : []
                    )
                )
            }
        );
    }
}
