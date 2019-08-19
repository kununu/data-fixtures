<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;

final class ElasticSearchFixture2 implements ElasticSearchFixtureInterface
{
    public function load(Client $elasticSearch, string $indexName): void
    {
    }
}
