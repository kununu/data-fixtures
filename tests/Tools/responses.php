<?php
declare(strict_types=1);

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

return [
    [
        'url'  => 'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data',
        'code' => 404,
    ],
    [
        'url'           => 'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data',
        'method'        => 'POST',
        'body'          => <<<'JSON'
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
        'bodyValidator' => function(MockResponse $response, array $options = []): MockResponse {
            $id = $options['json']['id'] ?? null;

            if ($id === 5000) {
                return $response;
            }

            return new MockResponse('', ['http_code' => Response::HTTP_NOT_FOUND]);
        },
    ],
];
