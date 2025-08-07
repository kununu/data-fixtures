<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Kununu\DataFixtures\FixtureInterface;
use OpenSearch\Client;

interface OpenSearchFixtureInterface extends FixtureInterface
{
    public function load(Client $client, string $indexName, bool $throwOnFail = true): void;
}
