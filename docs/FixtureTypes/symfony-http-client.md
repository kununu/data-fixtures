# Symfony Http Client Fixtures

The `Symfony Http Client Fixtures` allows you to load data fixtures for simulated Symfony Http Client requests.

This means you can do predictable tests without relying on external services to return the data you are expecting.

## Install

Before starting loading Http Client Fixtures make sure to add [Symfony Http Client](https://github.com/symfony/http-client) and [Symfony Http Foundation](https://github.com/symfony/http-foundation) as a dependencies of your project.

```shell
composer require symfony/http-client symfony/http-foundation
```

## How to load Http Client Fixtures?

### 1. Create fixture classes

The first step to load Http Client Fixtures is to create fixtures classes.

These classes must implement the [HttpClientFixtureInterface](../../src/Adapter/HttpClientFixtureInterface.php) or if you want to easily define an array of requests/responses on your fixtures you can extend the class [HttpClientPhpArrayFixture](../../src/Adapter/HttpClientPhpArrayFixture.php).

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Adapter\HttpClientPhpArrayFixture;

final class MyFixture extends HttpClientPhpArrayFixture
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . 'fixture1.php',
        ];
    }
}
```

Each included file in `fileNames` method should return a PHP array.

```php
<?php
declare(strict_types=1);

// One entry for each request you want to simulate
//
// Format:
// [
//      'url'           => Url of the request (REQUIRED)
//      'method'        => Http method (default: GET)
//      'code'          => Http code to return (default: 200)
//      'body'          => Body of the response (default: an empty string)
//      'bodyValidator' => A callable to validate the body (e.g. in POST requests) and change the response
//                         based on the body contents (default: null)
//
//                         The callable has the following signature:
// 
//                         function(MockResponse $response, array $options = []): MockResponse
//                          
//                          - $response - The MockResponse object that is the "normal" response
//                          - $options  - The request options (which contains the body, auth headers, etc.)
//
//                          Return value: A MockResponse object which can be the original or a new one
// ]
//
return [    
    [
        'url'  => 'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data',
        'code' => 404,
    ],
    [
        'url'  => 'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data',
        'body' => <<<'JSON'
{
    "id": 1000,
    "name": {
        "first": "The",
        "surname": "Name"
    },
    "age": 39,
    "newsletter": true
}
JSON
        ,
    [
        'url'           => 'https://my.server/data',
        'method'        => 'POST',
        // Example of how to manipulate the response based on the body context
        'bodyValidator' => function(MockResponse $response, array $options = []): MockResponse {
            // Get the id from the json body if available
            $id = $options['json']['id'] ?? null;

            // We only want a "good" response for a specific id
            if ($id === 5000) {
                return $response;
            }

            // For all other cases return a 404
            return new MockResponse('', ['http_code' => Response::HTTP_NOT_FOUND]);        
        }        
    ],
];
```

If a request is made to an url that is not loaded by a fixture the client will return a **Http 500** status code.

### 2. Load fixtures

In order to load the fixtures that you created in the previous step you will need to configure the *Http Client Executor*.

Note that you **need to** use the special [HttpClient](../../src/Tools/HttpClient.php) provided with this library which is based on Symfony **MockHttpClient**.

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Executor\HttpClientExecutor;
use Kununu\DataFixtures\Loader\HttpClientFixturesLoader;
use Kununu\DataFixtures\Purger\HttpClientPurger;
use Kununu\DataFixtures\Tools\HttpClient;

$httpClient = new HttpClient();

$purger = new HttpClientPurger($httpClient);

$executor = new HttpClientExecutor($httpClient, $purger);

$loader = new HttpClientFixturesLoader();

$loader->addFixture(new MyFixture());

$executor->execute($loader->getFixtures());
```

If you want to know more options on how you can load fixtures in the Loader checkout *[Load Fixtures](../../README.md#load-fixtures)*.

### 3. Append Fixtures

By default, when loading fixtures the Http Client responses internal storage is purged. If you want to change this behavior and instead append the fixtures, you can pass *true* as second argument to the `HttpClientExecutor`.

```php
$executor->execute($loader->getFixtures(), true);
```

---

[Back to Index](../../README.md)
