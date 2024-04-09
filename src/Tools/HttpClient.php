<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Generator;
use ReflectionClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpClient extends MockHttpClient implements FixturesHttpClientInterface
{
    private ?array $responses = null;
    private ?array $bodyValidators = null;

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $key = $this->responseKey($method, $url);
        $response = $this->responses[$key] ?? null;
        $bodyValidator = $this->bodyValidators[$key] ?? null;
        if (!$response instanceof MockResponse) {
            $response = new MockResponse('', ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        if (is_callable($bodyValidator)) {
            $response = $bodyValidator($response, $options);
        }

        $this->recreateResponseFactory([$response]);

        return parent::request($method, $url, $options);
    }

    public function addResponses(array $responses): void
    {
        if (null === $this->responses) {
            $this->responses = [];
        }

        if (null === $this->bodyValidators) {
            $this->bodyValidators = [];
        }

        foreach ($responses as $response) {
            [$url, $method, $code, $body, $bodyValidator] = $this->extractResponseData($response);
            $key = $this->responseKey($method, $url);
            if (is_callable($bodyValidator)) {
                $this->bodyValidators[$key] = $bodyValidator;
            }
            $this->responses[$key] = new MockResponse($body, ['http_code' => $code]);
        }
    }

    public function clearResponses(): void
    {
        $this->responses = [];
        $this->bodyValidators = [];
    }

    private function extractResponseData(array $response): array
    {
        return [
            $response['url'],
            $response['method'] ?? Request::METHOD_GET,
            (int) ($response['code'] ?? Response::HTTP_OK),
            $response['body'] ?? '',
            $response['bodyValidator'] ?? null,
        ];
    }

    private function responseKey(string $method, string $url): string
    {
        return sprintf('%s_%s', strtoupper($method), $url);
    }

    private function recreateResponseFactory(array $responses): void
    {
        $responseFactory = (static fn(): Generator => yield from $responses)();

        $reflectionClass = new ReflectionClass(MockHttpClient::class);
        $reflectionProperty = $reflectionClass->getProperty('responseFactory');
        $reflectionProperty->setValue($this, $responseFactory);
    }
}
