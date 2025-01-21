<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Kununu\DataFixtures\Exception\LoadFailedException;
use OpenSearch\Client;
use stdClass;

abstract class OpenSearchFileFixture extends AbstractFileLoaderFixture implements OpenSearchFixtureInterface
{
    use OpenSearchFixtureTrait;

    public function load(Client $client, string $indexName, bool $throwOnFail = true): void
    {
        parent::loadFiles(fn(array $documents) => $this->bulk($client, $indexName, $documents, $throwOnFail));
    }

    private function bulk(Client $client, string $indexName, array $documents, bool $throwOnFail): void
    {
        if (empty($documents)) {
            return;
        }

        $cenas = $this->prepareBodyForBulkIndexation($indexName, $documents);

        $result = $client->bulk([
            'body' => $this->prepareBodyForBulkIndexation($indexName, $documents),
        ]);

        if ($throwOnFail && ($result['errors'] ?? false)) {
            $errors = array_map(
                static fn(array $item): stdClass => (object) $item,
                array_filter($result['items'] ?? [])
            );

            throw new LoadFailedException(static::class, $errors);
        }
    }
}
