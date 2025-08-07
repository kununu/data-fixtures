<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\OpenSearchFixtureInterface;
use OpenSearch\Client;

final class OpenSearchFixture1 implements OpenSearchFixtureInterface
{
    public function load(Client $client, string $indexName, bool $throwOnFail = true): void
    {
    }
}
