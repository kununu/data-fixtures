<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\AbstractFileLoaderFixture;

final class InvalidLoadModeFixture extends AbstractFileLoaderFixture
{
    public function load(): void
    {
        parent::loadFiles(function($content): void {
        });
    }

    protected function fileNames(): array
    {
        return [
            __DIR__ . '/Other/file.txt',
        ];
    }

    protected function getFileExtension(): string
    {
        return 'txt';
    }

    protected function getLoadMode(): string
    {
        return 'invalid load mode';
    }
}
