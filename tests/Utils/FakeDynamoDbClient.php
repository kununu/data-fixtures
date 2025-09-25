<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Utils;

use ArrayIterator;
use Aws\CommandInterface;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\HandlerList;
use Aws\Result;
use Traversable;

class FakeDynamoDbClient extends DynamoDbClient
{
    private array $putItemCalls = [];
    private array $batchWriteItemCalls = [];
    private array $scanCalls = [];
    private array $describeTableCalls = [];
    private bool $shouldThrowOnPutItem = false;
    private bool $shouldThrowOnBatchWriteItem = false;
    private bool $shouldThrowOnDescribeTable = false;
    private bool $shouldThrowOnScan = false;
    private array $unprocessedItems = [];
    private bool $persistentUnprocessedItems = false;
    private array $scanResults = [];
    private array $tableDescriptions = [];
    private int $scanCallIndex = 0;

    public function __construct()
    {
        // Don't call parent constructor to avoid AWS configuration issues
    }

    public function putItem(array $args): Result
    {
        $this->putItemCalls[] = $args;

        if ($this->shouldThrowOnPutItem) {
            throw new DynamoDbException('Put item failed',
                $this->createMockAwsCommand(),
                ['message' => 'Put item failed', 'code' => 'ValidationException']
            );
        }

        return new Result(['Attributes' => []]);
    }

    public function batchWriteItem(array $args): Result
    {
        $this->batchWriteItemCalls[] = $args;

        if ($this->shouldThrowOnBatchWriteItem) {
            throw new DynamoDbException('Batch write failed',
                $this->createMockAwsCommand(),
                ['message' => 'Batch write failed', 'code' => 'ValidationException']
            );
        }

        // Return unprocessed items based on configuration
        $unprocessedItems = $this->unprocessedItems;

        // If not persistent, clear unprocessed items after first call
        if (!$this->persistentUnprocessedItems && count($this->batchWriteItemCalls) > 1) {
            $unprocessedItems = [];
        }

        return new Result(['UnprocessedItems' => $unprocessedItems]);
    }

    public function scan(array $args): Result
    {
        $this->scanCalls[] = $args;

        if ($this->shouldThrowOnScan) {
            throw new DynamoDbException('Scan failed',
                $this->createMockAwsCommand(),
                ['message' => 'Scan failed', 'code' => 'ValidationException']
            );
        }

        if (isset($this->scanResults[$this->scanCallIndex])) {
            return new Result($this->scanResults[$this->scanCallIndex++]);
        }

        return new Result(['Items' => [], 'LastEvaluatedKey' => null]);
    }

    public function describeTable(array $args): Result
    {
        $this->describeTableCalls[] = $args;

        if ($this->shouldThrowOnDescribeTable) {
            throw new DynamoDbException('Table not found',
                $this->createMockAwsCommand(),
                ['message' => 'Table not found', 'code' => 'ResourceNotFoundException']
            );
        }

        $tableName = $args['TableName'];
        if (isset($this->tableDescriptions[$tableName])) {
            return new Result($this->tableDescriptions[$tableName]);
        }

        return new Result([
            'Table' => [
                'KeySchema' => [
                    ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                ],
            ],
        ]);
    }

    // Test helper methods
    public function getPutItemCalls(): array
    {
        return $this->putItemCalls;
    }

    public function getBatchWriteItemCalls(): array
    {
        return $this->batchWriteItemCalls;
    }

    public function getScanCalls(): array
    {
        return $this->scanCalls;
    }

    public function getDescribeTableCalls(): array
    {
        return $this->describeTableCalls;
    }

    public function setPutItemThrowsException(bool $shouldThrow): void
    {
        $this->shouldThrowOnPutItem = $shouldThrow;
    }

    public function setBatchWriteItemThrowsException(bool $shouldThrow): void
    {
        $this->shouldThrowOnBatchWriteItem = $shouldThrow;
    }

    public function setDescribeTableThrowsException(bool $shouldThrow): void
    {
        $this->shouldThrowOnDescribeTable = $shouldThrow;
    }

    public function setScanThrowsException(bool $shouldThrow): void
    {
        $this->shouldThrowOnScan = $shouldThrow;
    }

    public function setUnprocessedItems(array $unprocessedItems): void
    {
        $this->unprocessedItems = $unprocessedItems;
    }

    public function setPersistentUnprocessedItems(bool $persistent): void
    {
        $this->persistentUnprocessedItems = $persistent;
    }

    public function setScanResults(array $scanResults): void
    {
        $this->scanResults = $scanResults;
        $this->scanCallIndex = 0;
    }

    public function setTableDescription(string $tableName, array $description): void
    {
        $this->tableDescriptions[$tableName] = $description;
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
}
