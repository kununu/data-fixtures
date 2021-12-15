<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use ReflectionClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpClient extends MockHttpClient implements FixturesHttpClientInterface
{
    private $responses;

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if (!($response = $this->responses[$this->responseKey($method, $url)] ?? null) instanceof MockResponse) {
            $response = new MockResponse('', ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        $this->recreateResponseFactory([$response]);

        return parent::request($method, $url, $options);
    }

    public function addResponses(array $responses): void
    {
        if (null === $this->responses) {
            $this->responses = [];
        }

        foreach ($responses as $response) {
            [$url, $method, $code, $body] = $this->extractResponseData($response);
            $this->responses[$this->responseKey($method, $url)] = new MockResponse($body, ['http_code' => $code]);
        }
    }

    public function clearResponses(): void
    {
        $this->responses = [];
    }

    private function extractResponseData(array $response): array
    {
        return [
            $response['url'],
            $response['method'] ?? Request::METHOD_GET,
            (int) ($response['code'] ?? Response::HTTP_OK),
            $response['body'] ?? '',
        ];
    }

    private function responseKey(string $method, string $url): string
    {
        return sprintf('%s_%s', strtoupper($method), $url);
    }

    private function recreateResponseFactory(array $responses): void
    {
        $responseFactory = (static function() use ($responses) {
            yield from $responses;
        })();

        $reflectionClass = new ReflectionClass(MockHttpClient::class);
        $reflectionProperty = $reflectionClass->getProperty('responseFactory');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this, $responseFactory);
    }
}
