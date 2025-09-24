<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Aws\DynamoDb\DynamoDbClient;
use Kununu\DataFixtures\Adapter\DynamoDbFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;

final readonly class DynamoDbExecutor implements ExecutorInterface
{
    public function __construct(
        private DynamoDbClient $dynamoDb,
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
    }

    private function load(DynamoDbFixtureInterface $fixture): void
    {
        $fixture->load($this->dynamoDb);
    }
}
