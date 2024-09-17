<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Elasticsearch\Client;
use InvalidArgumentException;
use Kununu\DataFixtures\Exception\LoadFailedException;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticsearchJsonDirectoryFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticsearchJsonDirectoryFixture2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticsearchJsonDirectoryFixtureTest extends TestCase
{
    private MockObject&Client $client;

    public function testLoadSuccess(): void
    {
        $bulk1 = [
            'body' => [
                [
                    'index' => [
                        '_index' => 'my_index',
                        '_id'    => '17e05f79-2c6e-4a71-bacf-afc8fd8e5f73',
                    ],
                ],
                [
                    'uuid'       => '17e05f79-2c6e-4a71-bacf-afc8fd8e5f73',
                    'name'       => 'Document 1',
                    'attributes' => [
                        'attrib_1' => 1,
                        'attrib_2' => 'active',
                        'attrib_3' => true,
                    ],
                ],
                [
                    'index' => [
                        '_index' => 'my_index',
                        '_id'    => 'e3d49639-a8f7-4b21-96d7-e4a22e87e1da',
                    ],
                ],
                [
                    'uuid'       => 'e3d49639-a8f7-4b21-96d7-e4a22e87e1da',
                    'name'       => 'Document 2',
                    'attributes' => [
                        'attrib_1' => 2,
                        'attrib_2' => 'inactive',
                        'attrib_3' => false,
                    ],
                ],
            ],
        ];
        $bulk2 = [
            'body' => [
                [
                    'index' => [
                        '_index' => 'my_index',
                        '_id'    => 'd1cd10a0-7023-434c-8cd3-0196e1d34c2f',
                    ],
                ],
                [
                    'uuid'       => 'd1cd10a0-7023-434c-8cd3-0196e1d34c2f',
                    'name'       => 'Document 3',
                    'attributes' => [
                        'attrib_1' => 3,
                        'attrib_2' => 'inactive',
                        'attrib_3' => false,
                    ],
                ],
            ],
        ];

        $this->client
            ->expects(self::exactly(2))
            ->method('bulk')
            ->with(
                self::callback(
                    static fn(array $bulk): bool => ($bulk == $bulk1 || $bulk == $bulk2)
                )
            )
            ->willReturn(['errors' => false]);

        (new ElasticsearchJsonDirectoryFixture1())->load($this->client, 'my_index');
    }

    public function testLoadWithErrors(): void
    {
        $bulk1 = [
            'body' => [
                [
                    'index' => [
                        '_index' => 'my_index',
                        '_id'    => '17e05f79-2c6e-4a71-bacf-afc8fd8e5f73',
                    ],
                ],
                [
                    'uuid'       => '17e05f79-2c6e-4a71-bacf-afc8fd8e5f73',
                    'name'       => 'Document 1',
                    'attributes' => [
                        'attrib_1' => 1,
                        'attrib_2' => 'active',
                        'attrib_3' => true,
                    ],
                ],
                [
                    'index' => [
                        '_index' => 'my_index',
                        '_id'    => 'e3d49639-a8f7-4b21-96d7-e4a22e87e1da',
                    ],
                ],
                [
                    'uuid'       => 'e3d49639-a8f7-4b21-96d7-e4a22e87e1da',
                    'name'       => 'Document 2',
                    'attributes' => [
                        'attrib_1' => 2,
                        'attrib_2' => 'inactive',
                        'attrib_3' => false,
                    ],
                ],
            ],
        ];

        $this->client
            ->expects(self::once())
            ->method('bulk')
            ->with(
                self::callback(
                    fn(array $bulk): bool => $bulk == $bulk1
                )
            )
            ->willReturn([
                'errors' => true,
                'items'  => [
                    [
                        'update' => [
                            'error'  => 'some error',
                            '_index' => 'my_index',
                            '_id'    => '17e05f79-2c6e-4a71-bacf-afc8fd8e5f73',
                            'status' => 'some status',
                        ],
                    ],
                ],
            ]);

        $this->expectException(LoadFailedException::class);
        $this->expectExceptionMessage(
            <<<'TEXT'
Errors:
[
    {
        "update": {
            "error": "some error",
            "_index": "my_index",
            "_id": "17e05f79-2c6e-4a71-bacf-afc8fd8e5f73",
            "status": "some status"
        }
    }
]
TEXT
        );

        (new ElasticsearchJsonDirectoryFixture1())->load($this->client, 'my_index');
    }

    public function testLoadWithInvalidJson(): void
    {
        $this->client
            ->expects(self::once())
            ->method('bulk')
            ->with([
                'body' => [
                    [
                        'index' => [
                            '_index' => 'my_index',
                            '_id'    => '17e05f79-2c6e-4a71-bacf-afc8fd8e5f73',
                        ],
                    ],
                    [
                        'uuid'       => '17e05f79-2c6e-4a71-bacf-afc8fd8e5f73',
                        'name'       => 'Document 1',
                        'attributes' => [
                            'attrib_1' => 1,
                            'attrib_2' => 'active',
                            'attrib_3' => true,
                        ],
                    ],
                    [
                        'index' => [
                            '_index' => 'my_index',
                            '_id'    => 'e3d49639-a8f7-4b21-96d7-e4a22e87e1da',
                        ],
                    ],
                    [
                        'uuid'       => 'e3d49639-a8f7-4b21-96d7-e4a22e87e1da',
                        'name'       => 'Document 2',
                        'attributes' => [
                            'attrib_1' => 2,
                            'attrib_2' => 'inactive',
                            'attrib_3' => false,
                        ],
                    ],
                ],
            ])
            ->willReturn(['errors' => false]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Error decoding JSON file: "%s"',
                realpath(__DIR__ . '/../TestFixtures/Elasticsearch/ElasticsearchJsonDirectoryFixture2/docs2.json')
            )
        );

        (new ElasticsearchJsonDirectoryFixture2())->load($this->client, 'my_index');
    }

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
    }
}
