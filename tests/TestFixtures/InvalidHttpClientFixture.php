<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\HttpClientPhpArrayFixture;

final class InvalidHttpClientFixture extends HttpClientPhpArrayFixture
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . '/fixture1.php',
        ];
    }
}
