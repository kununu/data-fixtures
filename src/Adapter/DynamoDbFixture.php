<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Aws\DynamoDb\DynamoDbClient;

abstract class DynamoDbFixture implements DynamoDbFixtureInterface
{
    use DynamoDbFixtureTrait;

    public function load(DynamoDbClient $dynamoDb, bool $throwOnFail = true): void
    {
        $records = $this->getRecords();

        match (true) {
            empty($records)       => null,
            count($records) === 1 => $this->loadSingleRecord($dynamoDb, $records[0], $throwOnFail),
            default               => $this->loadBatchRecords($dynamoDb, $records, $throwOnFail),
        };
    }
}
