<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DirectoryLoader;

use Kununu\DataFixtures\Adapter\HttpClientPhpArrayFixture;

abstract class HttpClientArrayDirectoryFixture extends HttpClientPhpArrayFixture
{
    use DirectoryFileSearchTrait;

    protected function getDirectory(): string
    {
        return 'Responses';
    }
}
