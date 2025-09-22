<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Tools;

use Kununu\DataFixtures\Tools\HttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpClientTest extends TestCase
{
    private const string REQUEST_1 = 'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data';
    private const string REQUEST_2 = 'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data';

    public function testHttpClient(): void
    {
        $httpClient = new HttpClient();
        $httpClient->addResponses(require_once __DIR__ . '/responses.php');

        $response = $httpClient->request(Request::METHOD_GET, self::REQUEST_1);

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $response = $httpClient->request(
            Request::METHOD_POST,
            self::REQUEST_2,
            [
                'json' => [
                    'id' => 5000,
                ],
            ]
        );

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            <<<'JSON'
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
            $response->getContent()
        );

        $response = $httpClient->request(Request::METHOD_POST, self::REQUEST_2);

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertEmpty($response->getContent(false));

        $httpClient->clearResponses();

        $response = $httpClient->request(Request::METHOD_GET, self::REQUEST_1);

        self::assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }
}
