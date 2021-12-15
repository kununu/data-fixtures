<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Kununu\DataFixtures\Exception\InvalidFileException;
use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use SplFileInfo;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

abstract class HttpClientPhpArrayFixture implements HttpClientFixtureInterface
{
    final public function load(HttpClientInterface $httpClient): void
    {
        if (!is_a($httpClient, FixturesHttpClientInterface::class)) {
            return;
        }

        foreach ($this->fileNames() as $fileName) {
            $file = new SplFileInfo($fileName);

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $httpClient->addResponses($this->loadFile($fileName));
        }
    }

    abstract protected function fileNames(): array;

    private function loadFile(string $fileName): array
    {
        if (file_exists($fileName) && is_readable($fileName)) {
            try {
                return include $fileName;
            } catch (Throwable $e) {
                throw new InvalidFileException($e->getMessage(), $e->getCode());
            }
        }

        throw new InvalidFileException(sprintf('Invalid file: %s', $fileName));
    }
}
