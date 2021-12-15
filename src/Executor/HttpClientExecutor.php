<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Kununu\DataFixtures\Adapter\HttpClientFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientExecutor implements ExecutorInterface
{
    private $httpClient;
    private $purger;

    public function __construct(HttpClientInterface $httpClient, PurgerInterface $purger)
    {
        $this->httpClient = $httpClient;
        $this->purger = $purger;
    }

    public function execute(array $fixtures, $append = false): void
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
