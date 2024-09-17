<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Kununu\DataFixtures\Purger\HttpClientPurger;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Kununu\DataFixtures\Tests\Utils\FakeHttpClientInterface;
use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;

final class HttpClientPurgerTest extends AbstractPurgerTestCase
{
    private (MockObject&FakeHttpClientInterface)|(MockObject&FixturesHttpClientInterface) $httpClient;

    public function testPurge(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('clearResponses');

        $this->purger->purge();
    }

    public function testPurgeWithNoFixturesHttpClient(): void
    {
        $this->httpClient = $this->createMock(FakeHttpClientInterface::class);
        $this->httpClient
            ->expects(self::never())
            ->method('clearResponses');

        $this->purger->purge();
    }

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(FixturesHttpClientInterface::class);

        parent::setUp();
    }

    protected function getPurger(): PurgerInterface
    {
        return new HttpClientPurger($this->httpClient);
    }
}
