<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

abstract class OpenSearchPhpArrayFixture extends OpenSearchFileFixture
{
    protected function getFileExtension(): string
    {
        return 'php';
    }

    protected function getLoadMode(): LoadMode
    {
        return LoadMode::Include;
    }
}
