<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Elasticsearch\Client;
use Kununu\DataFixtures\Exception\LoadFailedException;
use stdClass;

abstract class ElasticsearchFileFixture extends AbstractFileLoaderFixture implements ElasticsearchFixtureInterface
{
    use ElasticsearchFixtureTrait;

    public function load(Client $elasticSearch, string $indexName, bool $throwOnFail = true): void
    {
        parent::loadFiles(fn(array $documents) => $this->bulk($elasticSearch, $indexName, $documents, $throwOnFail));
    }

    private function bulk(Client $elasticSearch, string $indexName, array $documents, bool $throwOnFail): void
    {
        if (empty($documents)) {
            return;
        }

        $result = $elasticSearch->bulk(
            array_merge(
                ['body' => $this->prepareBodyForBulkIndexation($indexName, $documents)],
                is_string($documentType = $this->getDocumentType()) ? ['type' => $documentType] : []
            )
        );

        if ($throwOnFail && ($result['errors'] ?? false)) {
            $errors = array_map(
                static fn(array $item): stdClass => (object) $item,
                array_filter($result['items'] ?? [])
            );

            throw new LoadFailedException(static::class, $errors);
        }
    }
}
