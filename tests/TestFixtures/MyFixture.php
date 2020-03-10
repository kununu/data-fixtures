<?php declare(strict_types=1);

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticSearchFixture;

final class MyFixture extends ElasticSearchFixture
{
    public function load(Client $elasticSearch, string $indexName): void
    {
        $elasticSearch->bulk(
            [
                'type' => '_doc',
                'body' => $this->prepareBodyForBulkIndexation($indexName, $this->getYourDocuments()),
            ]
        );
    }

    /**
     * Implement this method to retrieve the document id on Elasticsearch from the document array
     * (or generate one)
     */
    protected function getDocumentIdForBulkIndexation(array $document)
    {
        return $document['id'];
    }

    /**
     * This method is an example of how to get documents to be bulk inserted
     */
    private function getYourDocuments(): array
    {
        return [
            [
                'id'        => 1,
                'doc_field' => 'Document 1',
            ],
            [
                'id'        => 2,
                'doc_field' => 'Document 2',
            ],
        ];
    }
}
