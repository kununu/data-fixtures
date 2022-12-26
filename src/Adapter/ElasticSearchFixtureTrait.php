<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

trait ElasticSearchFixtureTrait
{
    protected function prepareBodyForBulkIndexation(string $indexName, array $documents): array
    {
        $params = [];

        foreach ($documents as $document) {
            $params[] = [
                'index' => [
                    '_index' => $indexName,
                    '_id'    => $this->getDocumentIdForBulkIndexation($document),
                ],
            ];

            $params[] = $document;
        }

        return $params;
    }

    abstract protected function getDocumentIdForBulkIndexation(array $document);
}
