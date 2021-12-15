<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Kununu\DataFixtures\Purger\HttpClientPurger;
use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientPurgerTest extends TestCase
{
    public function testPurge(): void
    {
        $httpClient = $this->createMock(FixturesHttpClientInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('clearResponses');

        $purger = new HttpClientPurger($httpClient);
        $purger->purge();

        $this->assertTrue($purger->purgeWasExecuted());
    }

    public function testPurgeWithNoFixturesHttpClient(): void
    {
        $purger = new HttpClientPurger($this->createMock(HttpClientInterface::class));
        $purger->purge();

        $this->assertFalse($purger->purgeWasExecuted());
    }
}
