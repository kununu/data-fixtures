<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Result;
use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;
use Kununu\DataFixtures\Adapter\DynamoDbFixture;
use Kununu\DataFixtures\Exception\LoadFailedException;
use Kununu\DataFixtures\Tests\Utils\FakeDynamoDbClient;
use LogicException;
use PHPUnit\Framework\TestCase;

final class DynamoDbFixtureTest extends TestCase
{
    private FakeDynamoDbClient $dynamoDbClient;

    public function testLoadWithEmptyRecords(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users');
            }
        };

        $fixture->load($this->dynamoDbClient);

        self::assertEmpty($this->dynamoDbClient->getPutItemCalls());
        self::assertEmpty($this->dynamoDbClient->getBatchWriteItemCalls());
    }

    public function testLoadWithSingleRecord(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecord(new Record([Value::stringValue('id', 'user-1')]));
            }
        };

        $fixture->load($this->dynamoDbClient);

        $putItemCalls = $this->dynamoDbClient->getPutItemCalls();

        self::assertCount(1, $putItemCalls);
        self::assertEquals(
            [
                'TableName' => 'users',
                'Item'      => ['id' => ['S' => 'user-1']],
            ],
            $putItemCalls[0]
        );
        self::assertEmpty($this->dynamoDbClient->getBatchWriteItemCalls());
    }

    public function testLoadWithMultipleRecords(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecords([
                         new Record([Value::stringValue('id', 'user-1')]),
                         new Record([Value::stringValue('id', 'user-2')]),
                     ]);
            }
        };

        $fixture->load($this->dynamoDbClient);

        self::assertEmpty($this->dynamoDbClient->getPutItemCalls());

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(1, $batchWriteCalls);
        self::assertEquals(
            [
                'RequestItems' => [
                    'users' => [
                        ['PutRequest' => ['Item' => ['id' => ['S' => 'user-1']]]],
                        ['PutRequest' => ['Item' => ['id' => ['S' => 'user-2']]]],
                    ],
                ],
            ],
            $batchWriteCalls[0]
        );
    }

    public function testLoadWithThrowOnFailFalse(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecord(new Record([Value::stringValue('id', 'user-1')]));
            }
        };

        $fixture->load($this->dynamoDbClient, false);

        $putItemCalls = $this->dynamoDbClient->getPutItemCalls();
        self::assertCount(1, $putItemCalls);
    }

    public function testGetTableName(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('test-table');
            }
        };

        self::assertEquals('test-table', $fixture->getTableName());
    }

    public function testComplexFixture(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('user_profiles')
                     ->addRecords([
                         new Record([
                             Value::stringValue('user_id', 'user-123'),
                             Value::stringValue('name', 'John Doe'),
                             Value::numericValue('age', 30),
                             Value::boolValue('active', true),
                             Value::stringSetValue('tags', ['developer', 'php']),
                         ]),
                         new Record([
                             Value::stringValue('user_id', 'user-456'),
                             Value::stringValue('name', 'Jane Smith'),
                             Value::numericValue('age', 28),
                             Value::boolValue('active', false),
                             Value::mapValue('address', [
                                 'street' => ['S' => '123 Main St'],
                                 'city'   => ['S' => 'New York'],
                             ]),
                         ]),
                     ]);
            }
        };

        $fixture->load($this->dynamoDbClient);

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(1, $batchWriteCalls);

        $expectedRequestItems = [
            [
                'PutRequest' => [
                    'Item' => [
                        'user_id' => ['S' => 'user-123'],
                        'name'    => ['S' => 'John Doe'],
                        'age'     => ['N' => '30'],
                        'active'  => ['BOOL' => true],
                        'tags'    => ['SS' => ['developer', 'php']],
                    ],
                ],
            ],
            [
                'PutRequest' => [
                    'Item' => [
                        'user_id' => ['S' => 'user-456'],
                        'name'    => ['S' => 'Jane Smith'],
                        'age'     => ['N' => '28'],
                        'active'  => ['BOOL' => false],
                        'address' => ['M' => [
                            'street' => ['S' => '123 Main St'],
                            'city'   => ['S' => 'New York'],
                        ]],
                    ],
                ],
            ],
        ];

        self::assertEquals(
            [
                'RequestItems' => [
                    'user_profiles' => $expectedRequestItems,
                ],
            ],
            $batchWriteCalls[0]
        );

        self::assertEquals('user_profiles', $fixture->getTableName());
    }

    public function testSingleRecordFailureWithThrowOnFail(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecord(new Record([Value::stringValue('id', 'user-1')]));
            }
        };

        $this->dynamoDbClient->setPutItemThrowsException(true);

        $this->expectException(LoadFailedException::class);
        $this->expectExceptionMessage('Failed to load single record to table "users": Put item failed');

        $fixture->load($this->dynamoDbClient);
    }

    public function testSingleRecordFailureWithoutThrowOnFail(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecord(new Record([Value::stringValue('id', 'user-1')]));
            }
        };

        $this->dynamoDbClient->setPutItemThrowsException(true);

        $fixture->load($this->dynamoDbClient, false);

        $putItemCalls = $this->dynamoDbClient->getPutItemCalls();
        self::assertCount(1, $putItemCalls);
    }

    public function testBatchRecordsFailureWithThrowOnFail(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecords([
                         new Record([Value::stringValue('id', 'user-1')]),
                         new Record([Value::stringValue('id', 'user-2')]),
                     ]);
            }
        };

        $this->dynamoDbClient->setBatchWriteItemThrowsException(true);

        $this->expectException(LoadFailedException::class);
        $this->expectExceptionMessage('Failed to load batch records to table "users": Batch write failed');

        $fixture->load($this->dynamoDbClient);
    }

    public function testBatchRecordsFailureWithoutThrowOnFail(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecords([
                         new Record([Value::stringValue('id', 'user-1')]),
                         new Record([Value::stringValue('id', 'user-2')]),
                     ]);
            }
        };

        $this->dynamoDbClient->setBatchWriteItemThrowsException(true);

        $fixture->load($this->dynamoDbClient, false);

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(1, $batchWriteCalls);
    }

    public function testBatchRecordsWithChunking(): void
    {
        $records = [];
        for ($i = 1; $i <= 30; ++$i) {
            $records[] = new Record([Value::stringValue('id', "user-{$i}")]);
        }

        $fixture = new class($records) extends DynamoDbFixture {
            public function __construct(private readonly array $testRecords)
            {
            }

            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecords($this->testRecords);
            }
        };

        $fixture->load($this->dynamoDbClient);

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(2, $batchWriteCalls);

        $firstChunk = $batchWriteCalls[0]['RequestItems']['users'];
        self::assertCount(25, $firstChunk);

        $secondChunk = $batchWriteCalls[1]['RequestItems']['users'];
        self::assertCount(5, $secondChunk);
    }

    public function testBatchRecordsWithUnprocessedItemsRetrySuccess(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecords([
                         new Record([Value::stringValue('id', 'user-1')]),
                         new Record([Value::stringValue('id', 'user-2')]),
                     ]);
            }
        };

        $this->dynamoDbClient->setUnprocessedItems([]);

        $fixture->load($this->dynamoDbClient);

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(1, $batchWriteCalls);
    }

    public function testBatchRecordsUnprocessedItemsMaxRetriesExceeded(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecords([
                         new Record([Value::stringValue('id', 'user-1')]),
                         new Record([Value::stringValue('id', 'user-2')]),
                     ]);
            }
        };

        $unprocessedItems = [
            'users' => [
                ['PutRequest' => ['Item' => ['id' => ['S' => 'user-1']]]],
            ],
        ];
        $this->dynamoDbClient->setUnprocessedItems($unprocessedItems);
        $this->dynamoDbClient->setPersistentUnprocessedItems(true);

        $this->expectException(LoadFailedException::class);
        $this->expectExceptionMessage('Failed to process all items for table "users" after 3 retries');

        $fixture->load($this->dynamoDbClient);
    }

    public function testBatchRecordsUnprocessedItemsMaxRetriesExceededWithoutThrowOnFail(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users')
                     ->addRecords([
                         new Record([Value::stringValue('id', 'user-1')]),
                         new Record([Value::stringValue('id', 'user-2')]),
                     ]);
            }
        };

        $unprocessedItems = [
            'users' => [
                ['PutRequest' => ['Item' => ['id' => ['S' => 'user-1']]]],
            ],
        ];
        $this->dynamoDbClient->setUnprocessedItems($unprocessedItems);
        $this->dynamoDbClient->setPersistentUnprocessedItems(true);

        $fixture->load($this->dynamoDbClient, false);

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertGreaterThanOrEqual(1, count($batchWriteCalls));
    }

    public function testRecordManagementMethods(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('test-table');
            }

            public function publicAddRecord(Record $record): static
            {
                return $this->addRecord($record);
            }

            public function publicAddRecords(array $records): static
            {
                return $this->addRecords($records);
            }

            public function publicGetRecords(): array
            {
                return $this->getRecords();
            }
        };

        $record1 = new Record([Value::stringValue('id', 'record-1')]);
        $result = $fixture->publicAddRecord($record1);
        self::assertEquals($fixture, $result);

        $record2 = new Record([Value::stringValue('id', 'record-2')]);
        $record3 = new Record([Value::stringValue('id', 'record-3')]);
        $result = $fixture->publicAddRecords([$record2, $record3]);
        self::assertEquals($fixture, $result);

        $records = $fixture->publicGetRecords();
        self::assertCount(3, $records);
        self::assertEquals($record1, $records[0]);
        self::assertEquals($record2, $records[1]);
        self::assertEquals($record3, $records[2]);
    }

    public function testConfigurationIsOnlyCalledOnce(): void
    {
        $configureCallCount = 0;

        $fixture = new class($configureCallCount) extends DynamoDbFixture {
            public function __construct(private int &$configureCallCount)
            {
            }

            protected function configure(): void
            {
                ++$this->configureCallCount;
                $this->setTableName('test-table');
            }

            public function publicGetTableName(): string
            {
                return $this->getTableName();
            }

            public function publicGetRecords(): array
            {
                return $this->getRecords();
            }
        };

        $fixture->publicGetTableName();
        $fixture->publicGetRecords();
        $fixture->publicGetTableName();
        $fixture->publicGetRecords();

        self::assertEquals(1, $configureCallCount);
    }

    public function testSetTableNameFluentInterface(): void
    {
        $fixture = new class extends DynamoDbFixture {
            private bool $fluentTestPassed = false;

            protected function configure(): void
            {
                $result = $this->setTableName('fluent-test');
                $this->fluentTestPassed = ($result === $this);
            }

            public function wasFluentTestPassed(): bool
            {
                return $this->fluentTestPassed;
            }
        };

        $fixture->getTableName();
        self::assertTrue($fixture->wasFluentTestPassed());
        self::assertEquals('fluent-test', $fixture->getTableName());
    }

    public function testLoadBatchRecordsWithEmptyArray(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users');
            }

            public function testLoadBatchRecordsEmpty(DynamoDbClient $dynamoDb): void
            {
                $this->loadBatchRecords($dynamoDb, [], true);
            }
        };

        $fixture->testLoadBatchRecordsEmpty($this->dynamoDbClient);

        self::assertEmpty($this->dynamoDbClient->getBatchWriteItemCalls());
    }

    public function testHandleUnprocessedItemsRetryExceptionBreaksLoop(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this
                    ->setTableName('users')
                    ->addRecords([
                        new Record([Value::stringValue('id', 'user-1')]),
                        new Record([Value::stringValue('id', 'user-2')]),
                    ]);
            }
        };

        $callCount = 0;
        $client = $this->dynamoDbClient;
        $client->setNextBatchWriteItem(static function() use (&$callCount, $client) {
            ++$callCount;
            if ($callCount === 1) {
                $client->setNextBatchWriteItem(static function() use ($client): void {
                    throw new DynamoDbException(
                        'Retry failed',
                        $client->createMockAwsCommand(),
                        ['message' => 'Retry failed', 'code' => 'ValidationException']
                    );
                });

                return new Result(
                    [
                        'UnprocessedItems' => [
                            'users' => [
                                [
                                    'PutRequest' => [
                                        'Item' => [
                                            'id' => [
                                                'S' => 'user-1',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }

            throw new LogicException('Should not reach here');
        });

        $this->expectException(LoadFailedException::class);
        $this->expectExceptionMessage('Failed to process unprocessed items for table "users": Retry failed');

        $fixture->load($client);
    }

    public function testHandleUnprocessedItemsRetryExceptionWithoutThrowOnFail(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this
                    ->setTableName('users')
                    ->addRecords([
                        new Record([Value::stringValue('id', 'user-1')]),
                        new Record([Value::stringValue('id', 'user-2')]),
                    ]);
            }
        };

        $callCount = 0;
        $client = $this->dynamoDbClient;
        $client->setNextBatchWriteItem(static function() use (&$callCount, $client) {
            ++$callCount;
            if ($callCount === 1) {
                $client->setNextBatchWriteItem(static function() use ($client): void {
                    throw new DynamoDbException(
                        'Retry failed',
                        $client->createMockAwsCommand(),
                        ['message' => 'Retry failed', 'code' => 'ValidationException']
                    );
                });

                return new Result(
                    [
                        'UnprocessedItems' => [
                            'users' => [
                                [
                                    'PutRequest' => [
                                        'Item' => [
                                            'id' => [
                                                'S' => 'user-1',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }

            throw new LogicException('Should not reach here');
        });

        $fixture->load($client, false);

        $this->addToAssertionCount(1);
    }

    protected function setUp(): void
    {
        $this->dynamoDbClient = new FakeDynamoDbClient();
    }
}
