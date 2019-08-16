<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;

final class ElasticSearchExecutor implements ExecutorInterface
{
    private $elasticSearch;

    private $purger;

    public function __construct(Client $elasticSearch, PurgerInterface $purger)
    {
        $this->elasticSearch = $elasticSearch;
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
    }

    private function load(ElasticSearchFixtureInterface $fixture): void
    {
        $fixture->load($this->elasticSearch);
    }
}
