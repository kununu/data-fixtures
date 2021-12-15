<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientPurger implements PurgerInterface
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function purge(): void
    {
        if (!is_a($this->httpClient, FixturesHttpClientInterface::class)) {
            return;
        }

        $this->httpClient->clearResponses();
    }
}
