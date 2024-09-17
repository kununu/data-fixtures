<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class HttpClientPhpArrayFixture extends AbstractFileLoaderFixture implements HttpClientFixtureInterface
{
    final public function load(HttpClientInterface $httpClient): void
    {
        if (!is_a($httpClient, FixturesHttpClientInterface::class)) {
            return;
        }

        parent::loadFiles(fn(array $responses) => $httpClient->addResponses($responses));
    }

    protected function getFileExtension(): string
    {
        return 'php';
    }

    protected function getLoadMode(): LoadMode
    {
        return LoadMode::Include;
    }
}
