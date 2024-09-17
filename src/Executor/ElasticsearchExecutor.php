<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticsearchFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;

final readonly class ElasticsearchExecutor implements ExecutorInterface
{
    public function __construct(
        private Client $elasticSearch,
        private string $indexName,
        private PurgerInterface $purger,
    ) {
    }

    public function execute(array $fixtures, bool $append = false): void
    {
        if (false === $append) {
            $this->purger->purge();
        }

        foreach ($fixtures as $fixture) {
            $this->load($fixture);
        }

        $this->elasticSearch->indices()->refresh(['index' => $this->indexName]);
    }

    private function load(ElasticsearchFixtureInterface $fixture): void
    {
        $fixture->load($this->elasticSearch, $this->indexName);
    }
}
