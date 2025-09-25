<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use ArrayIterator;
use Aws\CommandInterface;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\HandlerList;
use Aws\Result;
use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;
use Kununu\DataFixtures\Adapter\DynamoDbFixture;
use Kununu\DataFixtures\Exception\LoadFailedException;
use Kununu\DataFixtures\Tests\Utils\FakeDynamoDbClient;
use PHPUnit\Framework\TestCase;
use Traversable;

final class DynamoDbFixtureTest extends TestCase
{
    private FakeDynamoDbClient $dynamoDbClient;

    protected function setUp(): void
    {
        $this->dynamoDbClient = new FakeDynamoDbClient();
    }

    public function testLoadWithEmptyRecords(): void
    {
        $fixture = new class extends DynamoDbFixture {
            protected function configure(): void
            {
                $this->setTableName('users');
            }
        };

        $fixture->load($this->dynamoDbClient);

        self::assertCount(0, $this->dynamoDbClient->getPutItemCalls());
        self::assertCount(0, $this->dynamoDbClient->getBatchWriteItemCalls());
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
        self::assertSame([
            'TableName' => 'users',
            'Item'      => ['id' => ['S' => 'user-1']],
        ], $putItemCalls[0]);

        self::assertCount(0, $this->dynamoDbClient->getBatchWriteItemCalls());
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

        self::assertCount(0, $this->dynamoDbClient->getPutItemCalls());

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(1, $batchWriteCalls);
        self::assertSame([
            'RequestItems' => [
                'users' => [
                    ['PutRequest' => ['Item' => ['id' => ['S' => 'user-1']]]],
                    ['PutRequest' => ['Item' => ['id' => ['S' => 'user-2']]]],
                ],
            ],
        ], $batchWriteCalls[0]);
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

        self::assertSame('test-table', $fixture->getTableName());
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

        self::assertSame([
            'RequestItems' => [
                'user_profiles' => $expectedRequestItems,
            ],
        ], $batchWriteCalls[0]);

        self::assertSame('user_profiles', $fixture->getTableName());
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
        self::assertSame($fixture, $result);

        $record2 = new Record([Value::stringValue('id', 'record-2')]);
        $record3 = new Record([Value::stringValue('id', 'record-3')]);
        $result = $fixture->publicAddRecords([$record2, $record3]);
        self::assertSame($fixture, $result);

        $records = $fixture->publicGetRecords();
        self::assertCount(3, $records);
        self::assertSame($record1, $records[0]);
        self::assertSame($record2, $records[1]);
        self::assertSame($record3, $records[2]);
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

        self::assertSame(1, $configureCallCount);
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
        self::assertSame('fluent-test', $fixture->getTableName());
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

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(0, $batchWriteCalls);
    }

    public function testHandleUnprocessedItemsRetryExceptionBreaksLoop(): void
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

        $this->dynamoDbClient = new class extends FakeDynamoDbClient {
            private int $callCount = 0;

            public function batchWriteItem(array $args): Result
            {
                ++$this->callCount;

                if ($this->callCount === 1) {
                    return new Result(['UnprocessedItems' => [
                        'users' => [
                            ['PutRequest' => ['Item' => ['id' => ['S' => 'user-1']]]],
                        ],
                    ]]);
                }

                throw new DynamoDbException(
                    'Retry failed',
                    $this->createMockAwsCommand(),
                    ['message' => 'Retry failed', 'code' => 'ValidationException']
                );
            }

            private function createMockAwsCommand(): object
            {
                return new class implements CommandInterface {
                    public function getName(): string
                    {
                        return 'MockCommand';
                    }

                    public function toArray(): array
                    {
                        return [];
                    }

                    public function hasParam($name): bool
                    {
                        return false;
                    }

                    public function getParam($name)
                    {
                        return null;
                    }

                    public function offsetExists($offset): bool
                    {
                        return false;
                    }

                    public function offsetGet($offset): mixed
                    {
                        return null;
                    }

                    public function offsetSet($offset, $value): void
                    {
                    }

                    public function offsetUnset($offset): void
                    {
                    }

                    public function count(): int
                    {
                        return 0;
                    }

                    public function getIterator(): Traversable
                    {
                        return new ArrayIterator([]);
                    }

                    public function getHandlerList(): HandlerList
                    {
                        return new HandlerList();
                    }
                };
            }
        };

        $this->expectException(LoadFailedException::class);
        $this->expectExceptionMessage('Failed to process unprocessed items for table "users": Retry failed');

        $fixture->load($this->dynamoDbClient);
    }

    public function testHandleUnprocessedItemsRetryExceptionWithoutThrowOnFail(): void
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

        $this->dynamoDbClient = new class extends FakeDynamoDbClient {
            private int $callCount = 0;

            public function batchWriteItem(array $args): Result
            {
                ++$this->callCount;

                if ($this->callCount === 1) {
                    return new Result(['UnprocessedItems' => [
                        'users' => [
                            ['PutRequest' => ['Item' => ['id' => ['S' => 'user-1']]]],
                        ],
                    ]]);
                }
                throw new DynamoDbException(
                    'Retry failed',
                    $this->createMockAwsCommand(),
                    ['message' => 'Retry failed', 'code' => 'ValidationException']
                );
            }

            private function createMockAwsCommand(): object
            {
                return new class implements CommandInterface {
                    public function getName(): string
                    {
                        return 'MockCommand';
                    }

                    public function toArray(): array
                    {
                        return [];
                    }

                    public function hasParam($name): bool
                    {
                        return false;
                    }

                    public function getParam($name)
                    {
                        return null;
                    }

                    public function offsetExists($offset): bool
                    {
                        return false;
                    }

                    public function offsetGet($offset): mixed
                    {
                        return null;
                    }

                    public function offsetSet($offset, $value): void
                    {
                    }

                    public function offsetUnset($offset): void
                    {
                    }

                    public function count(): int
                    {
                        return 0;
                    }

                    public function getIterator(): Traversable
                    {
                        return new ArrayIterator([]);
                    }

                    public function getHandlerList(): HandlerList
                    {
                        return new HandlerList();
                    }
                };
            }
        };

        $fixture->load($this->dynamoDbClient, false);

        $this->addToAssertionCount(1);
    }
}
