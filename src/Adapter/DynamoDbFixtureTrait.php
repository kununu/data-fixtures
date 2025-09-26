<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Exception\LoadFailedException;

trait DynamoDbFixtureTrait
{
    private string $tableName = '';
    private bool $isConfigured = false;

    /** @var array<int, Record> */
    private array $records = [];

    protected function setTableName(string $tableName): static
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function getTableName(): string
    {
        $this->initialize();

        return $this->tableName;
    }

    protected function addRecord(Record $record): static
    {
        $this->records[] = $record;

        return $this;
    }

    /** @param array<int, Record> $records */
    protected function addRecords(array $records): static
    {
        foreach ($records as $record) {
            $this->addRecord($record);
        }

        return $this;
    }

    /**
     * @return array<int, Record>
     */
    protected function getRecords(): array
    {
        $this->initialize();

        return $this->records;
    }

    protected function loadSingleRecord(DynamoDbClient $dynamoDb, Record $record, bool $throwOnFail = true): void
    {
        try {
            $dynamoDb->putItem([
                'TableName' => $this->getTableName(),
                'Item'      => $record->getValues(),
            ]);
        } catch (DynamoDbException $e) {
            if ($throwOnFail) {
                throw new LoadFailedException(
                    sprintf('Failed to load single record to table "%s": %s', $this->getTableName(), $e->getMessage()),
                    [$e->toArray()],
                );
            }
        }
    }

    /** @param array<int, Record> $records */
    protected function loadBatchRecords(DynamoDbClient $dynamoDb, array $records, bool $throwOnFail = true): void
    {
        if (empty($records)) {
            return;
        }

        // DynamoDB batch write supports up to 25 items per request
        $chunks = array_chunk($records, 25);

        foreach ($chunks as $chunk) {
            $requestItems = [];
            foreach ($chunk as $record) {
                $requestItems[] = [
                    'PutRequest' => [
                        'Item' => $record->getValues(),
                    ],
                ];
            }

            try {
                $result = $dynamoDb->batchWriteItem([
                    'RequestItems' => [
                        $this->getTableName() => $requestItems,
                    ],
                ]);

                // Handle unprocessed items
                if (!empty($result['UnprocessedItems'])) {
                    $this->handleUnprocessedItems($dynamoDb, $result['UnprocessedItems'], $throwOnFail);
                }
            } catch (DynamoDbException $e) {
                if ($throwOnFail) {
                    throw new LoadFailedException(
                        sprintf(
                            'Failed to load batch records to table "%s": %s',
                            $this->getTableName(),
                            $e->getMessage()
                        ),
                        [$e->toArray()],
                    );
                }
            }
        }
    }

    private function handleUnprocessedItems(DynamoDbClient $dynamoDb, array $unprocessedItems, bool $throwOnFail): void
    {
        $maxRetries = 3;
        $retryCount = 0;

        while (!empty($unprocessedItems) && $retryCount < $maxRetries) {
            ++$retryCount;

            // Exponential backoff: 100ms, 200ms, 300ms
            usleep(100000 * $retryCount);

            try {
                $result = $dynamoDb->batchWriteItem([
                    'RequestItems' => $unprocessedItems,
                ]);

                $unprocessedItems = $result['UnprocessedItems'] ?? [];
            } catch (DynamoDbException $e) {
                if ($throwOnFail) {
                    throw new LoadFailedException(
                        sprintf(
                            'Failed to process unprocessed items for table "%s": %s',
                            $this->getTableName(),
                            $e->getMessage()
                        ),
                        [$e->toArray()],
                    );
                }
                break;
            }
        }

        if (!empty($unprocessedItems) && $throwOnFail) {
            throw new LoadFailedException(
                sprintf(
                    'Failed to process all items for table "%s" after %d retries',
                    $this->getTableName(),
                    $maxRetries
                ),
                [$unprocessedItems],
            );
        }
    }

    private function initialize(): void
    {
        if (!$this->isConfigured) {
            $this->configure();
            $this->isConfigured = true;
        }
    }

    abstract protected function configure(): void;
}
