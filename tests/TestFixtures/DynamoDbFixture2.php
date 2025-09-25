<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;
use Kununu\DataFixtures\Adapter\DynamoDbFixture;

final class DynamoDbFixture2 extends DynamoDbFixture
{
    protected function configure(): void
    {
        $this->setTableName('products')
             ->addRecord(new Record([
                 Value::stringValue('id', 'product-1'),
                 Value::stringValue('name', 'Widget'),
                 Value::numericValue('price', 19.99),
             ]));
    }
}
