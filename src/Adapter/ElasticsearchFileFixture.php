<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Elasticsearch\Client;
use Kununu\DataFixtures\Exception\LoadFailedException;
use stdClass;

abstract class ElasticsearchFileFixture extends AbstractFileLoaderFixture implements ElasticsearchFixtureInterface
{
    use ElasticsearchFixtureTrait;

    private const INDEX = 'index';
    private const UPDATE = 'update';
    private const ERROR = 'error';

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
                fn(array $item): stdClass => (object) [
                    self::INDEX => $item[self::UPDATE]['_index'],
                    'id'        => $item[self::UPDATE]['_id'],
                    'status'    => $item[self::UPDATE]['status'],
                    self::ERROR => $item[self::UPDATE][self::ERROR],
                ],
                array_filter(
                    $result['items'] ?? [],
                    fn(array $item): bool => isset($item[self::UPDATE][self::ERROR])
                )
            );

            throw new LoadFailedException(static::class, $errors);
        }
    }
}
