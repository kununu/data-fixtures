<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Kununu\DataFixtures\Exception\InvalidFileException;
use Kununu\DataFixtures\Tests\TestFixtures\HttpClientFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\HttpClientFixture2;
use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientPhpArrayFixtureTest extends TestCase
{
    private $httpClient;

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
JSON
                    ,
                ],
            ]);

        $fixture = new HttpClientFixture1();
        $fixture->load($this->httpClient);

        $this->assertTrue($fixture->triedToLoadFeatures());
    }

    public function testFileNotFound(): void
    {
        $this->httpClient
            ->expects($this->never())
            ->method('addResponses');

        $this->expectException(InvalidFileException::class);

        (new InvalidHttpClientFixture())->load($this->httpClient);
    }

    public function testInvalidFile(): void
    {
        $this->httpClient
            ->expects($this->never())
            ->method('addResponses');

        $this->expectException(InvalidFileException::class);

        (new HttpClientFixture2())->load($this->httpClient);
    }

    public function testNotAFixtureHttpClient(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $fixture = new HttpClientFixture2();
        $fixture->load($httpClient);

        $this->assertFalse($fixture->triedToLoadFeatures());
    }

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(FixturesHttpClientInterface::class);
    }
}
