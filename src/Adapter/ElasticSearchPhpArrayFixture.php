<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

abstract class ElasticSearchPhpArrayFixture extends ElasticSearchFileFixture
{
    protected function getFileExtension(): string
    {
        return 'php';
    }

    protected function getLoadMode(): string
    {
        return self::LOAD_MODE_INCLUDE;
    }
}
