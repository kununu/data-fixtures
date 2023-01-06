<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Elasticsearch\Client;

abstract class ElasticSearchFileFixture extends AbstractFileLoaderFixture implements ElasticSearchFixtureInterface
{
    use ElasticSearchFixtureTrait;

    final public function load(Client $elasticSearch, string $indexName): void
    {
        parent::loadFiles(
            function(array $documents) use ($elasticSearch, $indexName): void {
                if (empty($documents)) {
                    return;
                }

                $elasticSearch->bulk(
                    array_merge(
                        ['body' => $this->prepareBodyForBulkIndexation($indexName, $documents)],
                        is_string($documentType = $this->getDocumentType()) ? ['type' => $documentType] : []
                    )
                );
            }
        );
    }
}
