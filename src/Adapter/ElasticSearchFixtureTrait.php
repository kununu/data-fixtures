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
                'index' => array_merge(
                    [
                        '_index' => $indexName,
                        '_id'    => $this->getDocumentIdForBulkIndexation($document),
                    ],
                    is_string($documentType = $this->getDocumentType()) ? ['_type' => $documentType] : []
                ),
            ];

            $params[] = $this->prepareDocument($document);
        }

        return $params;
    }

    abstract protected function getDocumentIdForBulkIndexation(array $document);

    protected function prepareDocument(array $document): array
    {
        return $document;
    }

    protected function getDocumentType(): ?string
    {
        return null;
    }
}
