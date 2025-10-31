# OpenSearch Fixtures

The `OpenSearch Fixtures` allows you to load data fixtures for any OpenSearch index.

## Install

Before starting loading OpenSearch Fixtures make sure to add [OpenSearch](https://github.com/opensearch-project/opensearch-php) as a dependency of your project.

```shell
composer require opensearch-project/opensearch-php
```

## How to load OpenSearch Fixtures?

### 1. Create fixture classes

The first step to load OpenSearch Fixtures is to create fixtures classes. This classes must implement the [OpenSearchFixtureInterface](../../src/Adapter/OpenSearchFixtureInterface.php) or if you want to easily use *bulk* inserts on your fixtures you can extend the class [OpenSearchFixture](../../src/Adapter/OpenSearchFixture.php).


#### Inserting a single document

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Adapter\OpenSearchFixtureInterface;
use OpenSearch\Client;

final class MyFixture implements OpenSearchFixtureInterface
{
    public function load(Client $client, string $indexName): void
    {
        $params = [
            'index' => $indexName,
            'id'    => 'my_id',
            'body'  => ['testField' => 'abc']
        ];

        $client->index($params);
    }
}
```

#### Bulk inserting documents

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Adapter\OpenSearchFixture;
use OpenSearch\Client;

final class MyFixture extends OpenSearchFixture
{
    public function load(Client $client, string $indexName): void
    {
        $client->bulk(
            [
                'body' => $this->prepareBodyForBulkIndexation($indexName, $this->getYourDocuments()),
            ]
        );
    }

    /**
     * Implement this method to retrieve the document id on OpenSearch from the document array
     * (or generate one)
     */
    protected function getDocumentIdForBulkIndexation(array $document): mixed
    {
        return sprintf('%d_%s', $document['id'], $document['doc_type']) ;
    }
    
    /**
     * Implement this method to change the document structure before sending it to OpenSearch (e.g. for removing
     * fields that are in your document array but should not go to OpenSearch).
     * If not overridden it will by default returns the document unchanged.  
     */
    protected function prepareDocument(array $document) : array
    {
        // In this example we don't want to send the 'doc_type' field because it is only being used to generate the
        // document id
        unset($document['doc_type']);
        
        return $document;
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
                'doc_type' => 'invoice',
            ],
            [
                'id'        => 2,
                'doc_field' => 'Document 2',
                'doc_type' => 'receipt',
            ],
        ];
    }
}
```

#### Bulk insert documents from files

To include a series of files extend `Kununu\DataFixtures\Adapter\OpenSearchFileFixture`.

There are two options here:

- Each file return an array of PHP files with the documents
- Each file has a JSON representation of an array of documents

Your fixtures class should implement the following methods:

- `protected function fileNames(): array;`

Get the files to load

- `protected function getFileExtension(): string;`

Get the file extension (should return `php` for PHP array files or `json` for JSON file)

- `protected function getLoadMode(): LoadMode;`

The load method to use.
- `LoadMode::Include` to include the PHP array files
- `LoadMode:LoadJson` to load and convert the JSON files to array

Example:

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Adapter\LoadMode;
use Kununu\DataFixtures\Adapter\OpenSearchhFileFixture;
use OpenSearch\Client;

final class MyFixture extends OpenSearchhFileFixture
{
	protected function fileNames(): array
	{
		// Load data from the following files
		return [
			__DIR__. '/OpenSearch/fixture1.php';
		];
	}
	
    protected function getFileExtension(): string
    {
		// Only load *.php files
        return 'php';
    }

    protected function getLoadMode(): LoadMode
    {
		// Load the php files as includes
        return LoadMode::Include;
    }

    /**
     * Implement this method to retrieve the document id on OpenSearch from the document array
     * (or generate one)
     */
    protected function getDocumentIdForBulkIndexation(array $document): mixed
    {
        return sprintf('%d_%s', $document['id'], $document['doc_type']) ;
    }
    
    /**
     * Implement this method to change the document structure before sending it to OpenSearch (e.g. for removing
     * fields that are in your document array but should not go to OpenSearch).
     * If not overridden it will by default returns the document unchanged.  
     */
    protected function prepareDocument(array $document) : array
    {
        // In this example we don't want to send the 'doc_type' field because it is only being used to generate the
        // document id
        unset($document['doc_type']);
        
        return $document;
    }
}
```

Or for JSON files:

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Adapter\LoadMode;
use Kununu\DataFixtures\Adapter\OpenSearchFileFixture;
use OpenSearch\Client;

final class MyFixture extends OpenSearchFileFixture
{
	protected function fileNames(): array
	{
		// Load data from the following files
		return [
			__DIR__. '/OpenSearch/fixture1.json';
		];
	}
	
    protected function getFileExtension(): string
    {
		// Only load *.json files
        return 'json';
    }

    protected function getLoadMode(): string
    {
		// Load the json files contents as arrays
        return LoadMode::LoadJson;
    }

    /**
     * Implement this method to retrieve the document id on OpenSearch from the document array
     * (or generate one)
     */
    protected function getDocumentIdForBulkIndexation(array $document): mixed
    {
        return sprintf('%d_%s', $document['id'], $document['doc_type']) ;
    }
    
    /**
     * Implement this method to change the document structure before sending it to OpenSearch (e.g. for removing
     * fields that are in your document array but should not go to OpenSearch).
     * If not overridden it will by default returns the document unchanged.  
     */
    protected function prepareDocument(array $document) : array
    {
        // In this example we don't want to send the 'doc_type' field because it is only being used to generate the
        // document id
        unset($document['doc_type']);
        
        return $document;
    }
}
```

*Fixtures files:*

##### fixture1.php

```php
<?php
declare(strict_types=1);

return [
    [
        'id'        => 1,
        'doc_field' => 'Document 1',
        'doc_type' => 'invoice',
    ],
    [
        'id'        => 2,
        'doc_field' => 'Document 2',
        'doc_type' => 'receipt',
    ],
];
```

##### fixture1.json

```json
[
    {
        "id": 1,
        "doc_field": "Document 1",
        "doc_type": "invoice"
    },
    {
        "id": 2,
        "doc_field": "Document 2",
        "doc_type": "receipt"
    }
]
```

### 2. Load fixtures

In order to load the fixtures that you created in the previous step you will need to configure the *OpenSearch Executor*.

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Executor\OpenSearchExecutor;
use Kununu\DataFixtures\Loader\OpenSearchFixturesLoader;
use Kununu\DataFixtures\Purger\OpenSearchPurger;
use OpenSearch\ClientBuilder;

$client = ClientBuilder::create()->build();

$purger = new OpenSearchPurger($client, 'my_index');

$executor = new OpenSearchExecutor($client, 'my_index', $purger);

$loader = new OpenSearchFixturesLoader();
$loader->addFixture(new MyFixture());

$executor->execute($loader->getFixtures());

// If you want you can `append` the fixtures instead of purging the index
$executor->execute($loader->getFixtures(), true);
```

If you want to know more options on how you can load fixtures in the Loader checkout *[Load Fixtures](../../README.md#load-fixtures)*.

### 3. Append Fixtures

By default, when loading fixtures the OpenSearch index is purged. If you want to change this behavior and instead append the fixtures, you can pass *true* as second argument to the OpenSearchExecutor.

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Executor\OpenSearchExecutor;

$executor = new OpenSearchExecutor($client, 'my_index', $purger);

// If you want you can `append` the fixtures instead of purging the OpenSearch index
$executor->execute($loader->getFixtures(), true);
```

## Notes

- The OpenSearch Purger runs a *deleteByQuery* query that matches all documents.
- OpenSearch Executor and OpenSearch Purger calls the refresh API after purging the index and load the fixtures.

---

[Back to Index](../../README.md)
