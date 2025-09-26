<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Aws\DynamoDb\DynamoDbClient;
use Kununu\DataFixtures\FixtureInterface;

interface DynamoDbFixtureInterface extends FixtureInterface
{
    public function load(DynamoDbClient $dynamoDb, bool $throwOnFail = true): void;

    public function getTableName(): string;
}
