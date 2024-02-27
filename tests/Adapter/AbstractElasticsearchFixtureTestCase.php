<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticsearchFixtureInterface;
use Kununu\DataFixtures\Exception\LoadFailedException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractElasticsearchFixtureTestCase extends TestCase
{
    protected MockObject|Client $client;

    protected const INDEX_NAME = 'my_index';

    #[DataProvider('loadDataProvider')]
    public function testLoad(bool $throwOnFail, array $expectedErrors = []): void
    {
        if ($throwOnFail && !empty($expectedErrors)) {
            $this->expectException(LoadFailedException::class);
        }

        $this->getElasticsearchFixtureInterface()->load($this->client, self::INDEX_NAME, $throwOnFail);
    }

    abstract public static function loadDataProvider(): array;

    abstract protected function getElasticsearchFixtureInterface(): ElasticsearchFixtureInterface;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
    }
}
