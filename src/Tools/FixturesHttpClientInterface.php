<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface FixturesHttpClientInterface extends HttpClientInterface
{
    public function addResponses(array $responses): void;

    public function clearResponses(): void;
}
