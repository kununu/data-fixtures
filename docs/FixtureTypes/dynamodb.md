# DynamoDB Fixtures

This document describes how to use DynamoDB fixtures to load test data into Amazon DynamoDB tables.

## Installation

Make sure you have the AWS SDK for PHP installed:

```bash
composer require aws/aws-sdk-php
```

## How to load DynamoDB Fixtures?

### 1. Create fixture classes

Start by creating fixture classes. These must extend the [DynamoDbFixture](../../src/Adapter/DynamoDbFixture.php) class.

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Adapter\DynamoDbFixture;
use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;

final class MyFixture extends DynamoDbFixture
{
    protected function configure(): void
    {
        $this
            ->setTableName('user_profiles')
            ->addRecords($this->buildRecords());
    }

    private function buildRecords(): array
    {
        return [
            new Record([
                Value::stringValue('user_id', 'user-123'),
                Value::stringValue('name', 'John Doe'),
                Value::numericValue('age', 30),
                Value::boolValue('active', true),
                Value::stringSetValue('tags', ['developer', 'php', 'aws']),
            ]),
            new Record([
                Value::stringValue('user_id', 'user-456'),
                Value::stringValue('name', 'Jane Smith'),
                Value::numericValue('age', 28),
                Value::boolValue('active', false),
                Value::mapValue('address', [
                    'street' => ['S' => '123 Main St'],
                    'city' => ['S' => 'New York'],
                ]),
            ]),
        ];
    }
}
```

### 2. Load fixtures

To load the fixtures, configure the DynamoDB client and use the appropriate executor and loader classes.

```php
<?php
declare(strict_types=1);

use Aws\DynamoDb\DynamoDbClient;
use Kununu\DataFixtures\Executor\DynamoDbExecutor;
use Kununu\DataFixtures\Loader\DynamoDbFixturesLoader;
use Kununu\DataFixtures\Purger\DynamoDbPurger;

$client = new DynamoDbClient([
    'region'   => 'us-east-1',
    'version'  => 'latest',
    'endpoint' => 'http://localhost:8000', // For local DynamoDB
    'credentials' => [
        'key'    => 'your-key',
        'secret' => 'your-secret',
    ],
]);

$purger = new DynamoDbPurger($client);

$executor = new DynamoDbExecutor($client, $purger);

$loader = new DynamoDbFixturesLoader();
$loader->addFixture(new MyFixture());

$executor->execute($loader->getFixtures());
```

### 3. Append Fixtures

By default, loading fixtures will purge the DynamoDB table. To append fixtures without purging, pass `true` as the second argument to the executor.

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Executor\DynamoDbExecutor;

$executor = new DynamoDbExecutor($client, $purger);

// Append fixtures instead of purging the table
$executor->execute($loader->getFixtures(), true);
```

### 4. Exclude tables

When not appending, all tables are purged by default. To exclude specific tables from being purged, pass them as the second argument to the purger.

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Purger\DynamoDbPurger;

$excludedTables = ['audit_logs', 'migrations'];
$purger = new DynamoDbPurger($client, $excludedTables);
```

---

[Back to Index](../../README.md)
