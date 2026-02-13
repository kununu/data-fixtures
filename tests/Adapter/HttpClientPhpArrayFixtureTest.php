<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Kununu\DataFixtures\Exception\InvalidFileException;
use Kununu\DataFixtures\Tests\TestFixtures\HttpClientFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\HttpClientFixture2;
use Kununu\DataFixtures\Tests\TestFixtures\InvalidHttpClientFixture;
use Kununu\DataFixtures\Tests\Utils\FakeHttpClientInterface;
use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HttpClientPhpArrayFixtureTest extends TestCase
{
    private MockObject&FixturesHttpClientInterface $httpClient;

    public function testLoad(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('addResponses')
            ->with([
                [
                    'url'  => 'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data',
                    'code' => 404,
                ],
                [
                    'url'  => 'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data',
                    'body' => <<<'JSON'
{
    "id": 1000,
    "name": {
        "first": "The",
        "surname": "Name"
    },
    "age": 39,
    "newsletter": true
}
JSON,
                ],
            ]);

        $fixture = new HttpClientFixture1();
        $fixture->load($this->httpClient);
    }

    public function testFileNotFound(): void
    {
        $this->httpClient
            ->expects($this->never())
            ->method('addResponses');

        $this->expectException(InvalidFileException::class);

        new InvalidHttpClientFixture()->load($this->httpClient);
    }

    public function testInvalidFile(): void
    {
        $this->httpClient
            ->expects($this->never())
            ->method('addResponses');

        $this->expectException(InvalidFileException::class);

        new HttpClientFixture2()->load($this->httpClient);
    }

    public function testNotAFixtureHttpClient(): void
    {
        $httpClient = $this->createStub(FakeHttpClientInterface::class);

        $this->httpClient
            ->expects($this->never())
            ->method('addResponses');

        $fixture = new HttpClientFixture2();
        $fixture->load($httpClient);
    }

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(FixturesHttpClientInterface::class);
    }
}
