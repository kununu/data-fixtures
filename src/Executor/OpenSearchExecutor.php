<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Kununu\DataFixtures\Adapter\OpenSearchFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use OpenSearch\Client;

final readonly class OpenSearchExecutor implements ExecutorInterface
{
    public function __construct(
        private Client $client,
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

        $this->client->indices()->refresh(['index' => $this->indexName]);
    }

    private function load(OpenSearchFixtureInterface $fixture): void
    {
        $fixture->load($this->client, $this->indexName);
    }
}
