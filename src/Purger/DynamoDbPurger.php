<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Kununu\DataFixtures\Exception\PurgeFailedException;

final readonly class DynamoDbPurger implements PurgerInterface
{
    /**
     * @param array<string> $tableNames
     */
    public function __construct(
        private DynamoDbClient $dynamoDb,
        private array $tableNames,
    ) {
    }

    public function purge(): void
    {
        foreach ($this->tableNames as $tableName) {
            $this->purgeTable($tableName);
        }
    }

    private function purgeTable(string $tableName): void
    {
        try {
            // Get table description to understand the key schema
            $tableDescription = $this->dynamoDb->describeTable(['TableName' => $tableName]);
            $keySchema = $tableDescription['Table']['KeySchema'];

            // Extract key attribute names
            $keyAttributes = [];
            foreach ($keySchema as $key) {
                $keyAttributes[] = $key['AttributeName'];
            }

            // Scan all items to get their keys
            $scanParams = [
                'TableName'            => $tableName,
                'ProjectionExpression' => implode(', ', $keyAttributes),
            ];

            do {
                $result = $this->dynamoDb->scan($scanParams);

                if (!empty($result['Items'])) {
                    $this->deleteItems($tableName, $result['Items'], $keyAttributes);
                }

                // Handle pagination
                $scanParams['ExclusiveStartKey'] = $result['LastEvaluatedKey'] ?? null;
            } while (isset($result['LastEvaluatedKey']));
        } catch (DynamoDbException $e) {
            throw new PurgeFailedException(
                sprintf('Failed to purge DynamoDB table "%s": %s', $tableName, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<string>                    $keyAttributes
     */
    private function deleteItems(string $tableName, array $items, array $keyAttributes): void
    {
        // DynamoDB batch write supports up to 25 items per request
        $chunks = array_chunk($items, 25);

        foreach ($chunks as $chunk) {
            $requestItems = [];
            foreach ($chunk as $item) {
                $key = [];
                foreach ($keyAttributes as $keyAttribute) {
                    if (isset($item[$keyAttribute])) {
                        $key[$keyAttribute] = $item[$keyAttribute];
                    }
                }

                if (!empty($key)) {
                    $requestItems[] = [
                        'DeleteRequest' => [
                            'Key' => $key,
                        ],
                    ];
                }
            }

            if (!empty($requestItems)) {
                $result = $this->dynamoDb->batchWriteItem([
                    'RequestItems' => [
                        $tableName => $requestItems,
                    ],
                ]);

                // Handle unprocessed items
                if (!empty($result['UnprocessedItems'])) {
                    $this->handleUnprocessedDeletes($result['UnprocessedItems']);
                }
            }
        }
    }

    private function handleUnprocessedDeletes(array $unprocessedItems): void
    {
        $maxRetries = 3;
        $retryCount = 0;

        while (!empty($unprocessedItems) && $retryCount < $maxRetries) {
            ++$retryCount;

            // Exponential backoff
            usleep(100000 * $retryCount); // 100ms, 200ms, 300ms

            $result = $this->dynamoDb->batchWriteItem([
                'RequestItems' => $unprocessedItems,
            ]);

            $unprocessedItems = $result['UnprocessedItems'] ?? [];
        }

        if (!empty($unprocessedItems)) {
            throw new PurgeFailedException(
                sprintf('Failed to delete all items after %d retries', $maxRetries)
            );
        }
    }
}
