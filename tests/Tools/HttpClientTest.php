<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Tools;

use Kununu\DataFixtures\Tools\HttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpClientTest extends TestCase
{
    private const REQUEST_1 = 'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data';
    private const REQUEST_2 = 'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data';

    private const RESPONSES = [
        [
            'url'  => 'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data',
            'code' => 404,
        ],
        [
            'url'  => 'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data',
            'body' => '{"id":1000,"name":{"first":"The","surname":"Name"},"age":39,"newsletter":true}',
        ],
    ];

    public function testHttpClient(): void
    {
        $httpClient = new HttpClient();
        $httpClient->addResponses(self::RESPONSES);

        $response = $httpClient->request(Request::METHOD_GET, self::REQUEST_1);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $response = $httpClient->request(Request::METHOD_GET, self::REQUEST_2);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
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
JSON
            ,
            $response->getContent()
        );

        $httpClient->clearResponses();

        $response = $httpClient->request(Request::METHOD_GET, self::REQUEST_1);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }
}
