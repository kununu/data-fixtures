<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class HttpClientPurger implements PurgerInterface
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function purge(): void
    {
        if (is_a($this->httpClient, FixturesHttpClientInterface::class)) {
            $this->httpClient->clearResponses();
        }
    }
}
