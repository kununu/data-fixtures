<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Utils;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface FakeHttpClientInterface extends HttpClientInterface
{
    public function clearResponses(): void;
}
