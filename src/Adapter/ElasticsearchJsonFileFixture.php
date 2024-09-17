<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

abstract class ElasticsearchJsonFileFixture extends ElasticsearchFileFixture
{
    protected function getFileExtension(): string
    {
        return 'json';
    }

    protected function getLoadMode(): LoadMode
    {
        return LoadMode::LoadJson;
    }
}
