<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientPurger implements PurgerInterface
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function purge(): void
    {
        match (true) {
            is_a($this->httpClient, FixturesHttpClientInterface::class) => $this->httpClient->clearResponses(),
            default                                                     => null
        };
    }
}
