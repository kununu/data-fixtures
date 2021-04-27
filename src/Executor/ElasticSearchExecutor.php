<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;

final class ElasticSearchExecutor implements ExecutorInterface
{
    private $elasticSearch;

    private $indexName;

    private $purger;

    public function __construct(Client $elasticSearch, string $indexName, PurgerInterface $purger)
    {
        $this->elasticSearch = $elasticSearch;
        $this->indexName = $indexName;
        $this->purger = $purger;
    }

    public function execute(array $fixtures, $append = false): void
    {
        if (false === $append) {
            $this->purger->purge();
        }

        foreach ($fixtures as $fixture) {
            $this->load($fixture);
        }

        $this->elasticSearch->indices()->flush(['index' => $this->indexName, 'force' => true]);
        $this->elasticSearch->indices()->clearCache(['index' => $this->indexName]);
    }

    private function load(ElasticSearchFixtureInterface $fixture): void
    {
        $fixture->load($this->elasticSearch, $this->indexName);
    }
}
