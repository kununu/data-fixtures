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

        if (empty($records)) {
            return;
        }

        if (count($records) === 1) {
            $this->loadSingleRecord($dynamoDb, $records[0], $throwOnFail);
        } else {
            $this->loadBatchRecords($dynamoDb, $records, $throwOnFail);
        }
    }
}
