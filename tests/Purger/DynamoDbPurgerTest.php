<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Kununu\DataFixtures\Exception\PurgeFailedException;
use Kununu\DataFixtures\Purger\DynamoDbPurger;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Kununu\DataFixtures\Tests\Utils\FakeDynamoDbClient;

final class DynamoDbPurgerTest extends AbstractPurgerTestCase
{
    private FakeDynamoDbClient $dynamoDbClient;

    public function testPurgeEmptyTable(): void
    {
        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        [
                            'AttributeName' => 'id',
                            'KeyType'       => 'HASH',
                        ],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                [
                    'Items'            => [],
                    'LastEvaluatedKey' => null,
                ],
            ]
        );

        $this->purger->purge();

        $describeTableCalls = $this->dynamoDbClient->getDescribeTableCalls();
        self::assertCount(1, $describeTableCalls);
        self::assertEquals(['TableName' => 'users'], $describeTableCalls[0]);

        $scanCalls = $this->dynamoDbClient->getScanCalls();
        self::assertCount(1, $scanCalls);
        self::assertEquals(
            [
                'TableName'            => 'users',
                'ProjectionExpression' => 'id',
            ], $scanCalls[0]
        );

        self::assertEmpty($this->dynamoDbClient->getBatchWriteItemCalls());
    }

    public function testPurgeSingleTable(): void
    {
        $items = [
            ['id' => ['S' => 'user-1']],
            ['id' => ['S' => 'user-2']],
        ];

        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        [
                            'AttributeName' => 'id',
                            'KeyType'       => 'HASH',
                        ],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                [
                    'Items'            => $items,
                    'LastEvaluatedKey' => null,
                ],
            ]
        );

        $this->purger->purge();

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(1, $batchWriteCalls);
        self::assertEquals(
            [
                'RequestItems' => [
                    'users' => [
                        ['DeleteRequest' => ['Key' => ['id' => ['S' => 'user-1']]]],
                        ['DeleteRequest' => ['Key' => ['id' => ['S' => 'user-2']]]],
                    ],
                ],
            ],
            $batchWriteCalls[0]
        );
    }

    public function testPurgeMultipleTables(): void
    {
        $purger = new DynamoDbPurger($this->dynamoDbClient, ['users', 'products']);

        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        [
                            'AttributeName' => 'id',
                            'KeyType'       => 'HASH',
                        ],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setTableDescription(
            'products',
            [
                'Table' => [
                    'KeySchema' => [
                        [
                            'AttributeName' => 'product_id',
                            'KeyType'       => 'HASH',
                        ],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                ['Items' => [], 'LastEvaluatedKey' => null],
                ['Items' => [], 'LastEvaluatedKey' => null],
            ]
        );

        $purger->purge();

        $describeTableCalls = $this->dynamoDbClient->getDescribeTableCalls();
        self::assertCount(2, $describeTableCalls);
        self::assertEquals(['TableName' => 'users'], $describeTableCalls[0]);
        self::assertEquals(['TableName' => 'products'], $describeTableCalls[1]);
    }

    public function testPurgeWithCompositeKey(): void
    {
        $purger = new DynamoDbPurger($this->dynamoDbClient, ['orders']);

        $items = [
            ['user_id' => ['S' => 'user-1'], 'order_id' => ['S' => 'order-1']],
            ['user_id' => ['S' => 'user-2'], 'order_id' => ['S' => 'order-2']],
        ];

        $this->dynamoDbClient->setTableDescription(
            'orders',
            [
                'Table' => [
                    'KeySchema' => [
                        ['AttributeName' => 'user_id', 'KeyType' => 'HASH'],
                        ['AttributeName' => 'order_id', 'KeyType' => 'RANGE'],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                ['Items' => $items, 'LastEvaluatedKey' => null],
            ]
        );

        $purger->purge();

        $scanCalls = $this->dynamoDbClient->getScanCalls();
        self::assertCount(1, $scanCalls);
        self::assertEquals(
            [
                'TableName'            => 'orders',
                'ProjectionExpression' => 'user_id, order_id',
            ],
            $scanCalls[0]
        );

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(1, $batchWriteCalls);
        self::assertEquals(
            [
                'RequestItems' => [
                    'orders' => [
                        [
                            'DeleteRequest' => [
                                'Key' => [
                                    'user_id' => [
                                        'S' => 'user-1',
                                    ],
                                    'order_id' => [
                                        'S' => 'order-1'],
                                ],
                            ],
                        ],
                        [
                            'DeleteRequest' => [
                                'Key' => [
                                    'user_id' => [
                                        'S' => 'user-2',
                                    ],
                                    'order_id' => [
                                        'S' => 'order-2',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $batchWriteCalls[0]
        );
    }

    public function testPurgeWithPagination(): void
    {
        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                [
                    'Items'            => [['id' => ['S' => 'user-1']]],
                    'LastEvaluatedKey' => ['id' => ['S' => 'user-1']],
                ],
                [
                    'Items'            => [['id' => ['S' => 'user-2']]],
                    'LastEvaluatedKey' => null,
                ],
            ]
        );

        $this->purger->purge();

        $scanCalls = $this->dynamoDbClient->getScanCalls();
        self::assertCount(2, $scanCalls);

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(2, $batchWriteCalls);
    }

    public function testPurgeWithLargeNumberOfItems(): void
    {
        $items = [];
        for ($i = 1; $i <= 30; ++$i) {
            $items[] = ['id' => ['S' => sprintf('user-%d', $i)]];
        }

        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                ['Items' => $items, 'LastEvaluatedKey' => null],
            ]
        );

        $this->purger->purge();

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(2, $batchWriteCalls);
    }

    public function testPurgeWithUnprocessedItems(): void
    {
        $items = [['id' => ['S' => 'user-1']]];

        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                ['Items' => $items, 'LastEvaluatedKey' => null],
            ]
        );

        $this->dynamoDbClient->setUnprocessedItems([]);

        $this->purger->purge();

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertGreaterThanOrEqual(1, count($batchWriteCalls));
    }

    public function testPurgeThrowsExceptionOnDescribeTableFailure(): void
    {
        $this->dynamoDbClient->setDescribeTableThrowsException(true);

        $this->expectException(PurgeFailedException::class);
        $this->expectExceptionMessage('Failed to purge DynamoDB table "users": Table not found');

        $this->purger->purge();
    }

    public function testPurgeThrowsExceptionOnScanFailure(): void
    {
        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanThrowsException(true);

        $this->expectException(PurgeFailedException::class);
        $this->expectExceptionMessage('Failed to purge DynamoDB table "users": Scan failed');

        $this->purger->purge();
    }

    public function testPurgeWithUnprocessedDeletesRetrySuccess(): void
    {
        $items = [['id' => ['S' => 'user-1']]];

        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                ['Items' => $items, 'LastEvaluatedKey' => null],
            ]
        );

        $unprocessedItems = [
            'users' => [
                ['DeleteRequest' => ['Key' => ['id' => ['S' => 'user-1']]]],
            ],
        ];
        $this->dynamoDbClient->setUnprocessedItems($unprocessedItems);

        $this->purger->purge();

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertGreaterThanOrEqual(2, count($batchWriteCalls));
    }

    public function testPurgeThrowsExceptionOnMaxRetriesExceeded(): void
    {
        $items = [['id' => ['S' => 'user-1']]];
        $unprocessedItems = [
            'users' => [
                ['DeleteRequest' => ['Key' => ['id' => ['S' => 'user-1']]]],
            ],
        ];

        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                ['Items' => $items, 'LastEvaluatedKey' => null],
            ]
        );

        $this->dynamoDbClient->setUnprocessedItems($unprocessedItems);
        $this->dynamoDbClient->setPersistentUnprocessedItems(true);

        $this->expectException(PurgeFailedException::class);
        $this->expectExceptionMessage('Failed to delete all items after 3 retries');

        $this->purger->purge();
    }

    public function testPurgeWithItemsMissingKeyAttributes(): void
    {
        $items = [
            ['id' => ['S' => 'user-1']],
            ['name' => ['S' => 'John']],
            ['id'   => ['S' => 'user-2']],
        ];

        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                ['Items' => $items, 'LastEvaluatedKey' => null],
            ]
        );

        $this->purger->purge();

        $batchWriteCalls = $this->dynamoDbClient->getBatchWriteItemCalls();
        self::assertCount(1, $batchWriteCalls);

        $deleteRequests = $batchWriteCalls[0]['RequestItems']['users'];
        self::assertCount(2, $deleteRequests);
        self::assertEquals(['DeleteRequest' => ['Key' => ['id' => ['S' => 'user-1']]]], $deleteRequests[0]);
        self::assertEquals(['DeleteRequest' => ['Key' => ['id' => ['S' => 'user-2']]]], $deleteRequests[1]);
    }

    public function testPurgeWithAllItemsMissingKeyAttributes(): void
    {
        $items = [
            ['name' => ['S' => 'John']],
            ['email' => ['S' => 'john@example.com']],
        ];

        $this->dynamoDbClient->setTableDescription(
            'users',
            [
                'Table' => [
                    'KeySchema' => [
                        ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                    ],
                ],
            ]
        );

        $this->dynamoDbClient->setScanResults(
            [
                ['Items' => $items, 'LastEvaluatedKey' => null],
            ]
        );

        $this->purger->purge();

        self::assertEmpty($this->dynamoDbClient->getBatchWriteItemCalls());
    }

    public function testPurgeWithEmptyTableNames(): void
    {
        $purger = new DynamoDbPurger($this->dynamoDbClient, []);

        $purger->purge();

        self::assertEmpty($this->dynamoDbClient->getDescribeTableCalls());
        self::assertEmpty($this->dynamoDbClient->getScanCalls());
        self::assertEmpty($this->dynamoDbClient->getBatchWriteItemCalls());
    }

    protected function setUp(): void
    {
        $this->dynamoDbClient = new FakeDynamoDbClient();

        parent::setUp();
    }

    protected function getPurger(): PurgerInterface
    {
        return new DynamoDbPurger($this->dynamoDbClient, ['users']);
    }
}
