<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticsearchFixtureInterface;

final class ElasticsearchFixture2 implements ElasticsearchFixtureInterface
{
    public function load(Client $elasticSearch, string $indexName, bool $throwOnFail = true): void
    {
    }
}
