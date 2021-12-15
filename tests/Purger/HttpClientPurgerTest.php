<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Kununu\DataFixtures\Purger\HttpClientPurger;
use Kununu\DataFixtures\Tests\Utils\FakeHttpClientInterface;
use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use PHPUnit\Framework\TestCase;

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
    }

    public function testPurgeWithNoFixturesHttpClient(): void
    {
        $httpClient = $this->createMock(FakeHttpClientInterface::class);
        $httpClient
            ->expects($this->never())
            ->method('clearResponses');

        $purger = new HttpClientPurger($httpClient);
        $purger->purge();
    }
}
