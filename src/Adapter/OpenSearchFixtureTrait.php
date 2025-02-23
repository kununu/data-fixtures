<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

trait OpenSearchFixtureTrait
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

            $params[] = $this->prepareDocument($document);
        }

        return $params;
    }

    protected function prepareDocument(array $document): array
    {
        return $document;
    }

    abstract protected function getDocumentIdForBulkIndexation(array $document): mixed;
}
