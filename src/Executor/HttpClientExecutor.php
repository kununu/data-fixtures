<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Kununu\DataFixtures\Adapter\HttpClientFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientExecutor implements ExecutorInterface
{
    public function __construct(private HttpClientInterface $httpClient, private PurgerInterface $purger)
    {
    }

    public function execute(array $fixtures, bool $append = false): void
    {
        if ($append === false) {
            $this->purger->purge();
        }

        foreach ($fixtures as $fixture) {
            $this->load($fixture);
        }
    }

    private function load(HttpClientFixtureInterface $fixture): void
    {
        $fixture->load($this->httpClient);
    }
}
