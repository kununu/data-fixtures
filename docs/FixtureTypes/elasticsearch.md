# Elasticsearch Fixtures
---------------------

The `ElasticsearchFixtures` allows you to load data fixtures for any Elasticsearch index.

## Install

Before starting loading Elasticsearch Fixtures make sure to add [Elasticsearch](https://github.com/elastic/elasticsearch) as a dependency of your project.

```bash
composer require elastic/elasticsearch
```

## How to load Elasticsearch Fixtures?

### 1. Create fixture classes

The first step to load Elasticsearch Fixtures is to create fixtures classes. This classes must implement the [ElasticSearchFixtureInterface](https://github.com/kununu/data-fixtures/blob/master/src/Adapter/ElasticSearchFixtureInterface.php) or if you want to easily use *bulk* inserts on your fixtures you can extend the class [ElasticSearchFixture](https://github.com/kununu/data-fixtures/blob/master/src/Adapter/ElasticSearchFixture.php).


```php
use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;

final class MyFixture implements ElasticSearchFixtureInterface
{
    public function load(Client $elasticSearch, string $indexName): void
    {
        $params = [
            'index' => $indexName,
            'id'    => 'my_id',
            'body'  => ['testField' => 'abc']
        ];

        $elasticSearch->index($params);
    }
}
```

```php
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
```

### 2. Load fixtures

In order to load the fixtures that you created in the previous step, you will need to configure the *Elasticsearch Executor*.

```php
$client = \Elasticsearch\ClientBuilder::create()->build();

$purger = new \Kununu\DataFixtures\Purger\ElasticSearchPurger($client, 'my_index');

$executor = new \Kununu\DataFixtures\Executor\ElasticSearchExecutor($client, 'my_index', $purger);

$loader = new \Kununu\DataFixtures\Loader\ElasticSearchFixturesLoader();
$loader->addFixture(new MyFixture());

$executor->execute($loader->getFixtures());

// If you want you can `append` the fixtures instead of purging the index
$executor->execute($loader->getFixtures(), true);
```
