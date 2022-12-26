<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

abstract class ElasticSearchJsonFileFixture extends ElasticSearchFileFixture
{
    protected function getFileExtension(): string
    {
        return 'json';
    }

    protected function getLoadMode(): string
    {
        return self::LOAD_MODE_LOAD_JSON;
    }
}
