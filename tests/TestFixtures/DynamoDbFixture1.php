<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;
use Kununu\DataFixtures\Adapter\DynamoDbFixture;

final class DynamoDbFixture1 extends DynamoDbFixture
{
    protected function configure(): void
    {
        $this
            ->setTableName('users')
            ->addRecord(
                new Record([
                    Value::stringValue('id', 'user-1'),
                    Value::stringValue('name', 'John Doe'),
                ])
            );
    }
}
