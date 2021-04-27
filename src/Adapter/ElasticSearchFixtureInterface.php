<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Elasticsearch\Client;
use Kununu\DataFixtures\FixtureInterface;

interface ElasticSearchFixtureInterface extends FixtureInterface
{
    public function load(Client $elasticSearch, string $indexName): void;
}
