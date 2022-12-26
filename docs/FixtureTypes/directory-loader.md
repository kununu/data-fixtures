# Directory Loader

Some fixture types give you the option to load the fixtures from files. In order to help you even further, we have included *directory loader* fixtures.

Basically this type of features will get the content to load on the target data store from files.

## Usage

## ConnectionSqlDirectoryFixture

For any fixture extending this class:

- In the **same directory** as your fixture class create a `Sql` subdirectory.
- Inside the `Sql` directory create a new subdirectory with the **same name as your class**.
- All `*.sql` files inside that directory will be loaded when invoking the `load` method of that fixture class.
  - Other files will be ignored.

## ElasticSearchArrayDirectoryFixture

For any fixture extending this class:

- In the **same directory** as your fixture class create an `Elasticsearch` subdirectory.
- Inside the `Elasticsearch` directory create a new subdirectory with the **same name as your class**.
- All `*.php` files inside that directory will be loaded when invoking the `load` method of that fixture class.
  - Other files will be ignored.
- Each of your `*.php` file should return an array of arrays, where each entry in the main array is a representation of the Elasticsearch document.
- Your fixture class must also implement the `getDocumentIdForBulkIndexation` method as described in [Elasticsearch Fixtures](elasticsearch.md).

## ElasticSearchJsonDirectoryFixture

For any fixture extending this class:

- In the **same directory** as your fixture class create an `Elasticsearch` subdirectory.
- Inside the `Elasticsearch` directory create a new subdirectory with the **same name as your class**.
- All `*.json` files inside that directory will be loaded when invoking the `load` method of that fixture class.
    - Other files will be ignored.
- Each of your `*.json` file should be a JSON array of JSON objects, where each object in the main array is a representation of the Elasticsearch document.
- Each file will be decoded to a PHP array, and from there the workflow is the same as `ElasticSearchArrayDirectoryFixture`.
- Your fixture class must also implement the `getDocumentIdForBulkIndexation` method as described in [Elasticsearch Fixtures](elasticsearch.md).

## HttpClientArrayDirectoryFixture

For any fixture extending this class:

- In the **same directory** as your fixture class create an `Responses` subdirectory.
- Inside the `Responses` directory create a new subdirectory with the **same name as your class**.
- All `*.php` files inside that directory will be loaded when invoking the `load` method of that fixture class.
    - Other files will be ignored.
- Each of your `*.php` file should return an array as described in [Symfony Http Client Fixtures](symfony-http-client.md)  

### Structure

Your fixtures should be at directory level like this:

```
+- MyFixtureClasses\
  * MyConnectionFixture1.php
  * MyConnectionFixture2.php
  * MyHttpClientFixture1.php
  * MyElasticSearchFixtureFromJson.php
  +- Sql\
  | +- MyConnectionFixture1\
  | |   * sql-file.sql
  | |   * the-filename-is-up-to-you.sql
  | +- MyConnectionFixture2\
  | |   * this-file-is-ignored-because-it-has-a-different-extension.txt
  | |   * my-fixtures.sql
  +- Elasticsearch\
  | +- MyElasticSearchFixtureFromArray\
  | |   * my-elasticsearch-docs-array1.php
  | |   * my-elasticsearch-docs-array2.php
  | +- MyElasticSearchFixtureFromJson\
  | |   * my-elasticsearch-docs-json1.json
  | |   * my-elasticsearch-docs-json2.json
  +- Responses\
  | +- MyHttpClientFixture1\
  | |   * my-response-array1.php
  | |   * my-response-array2.php
```
