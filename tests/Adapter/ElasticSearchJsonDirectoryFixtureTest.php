<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Elasticsearch\Client;
use InvalidArgumentException;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticSearchJsonDirectoryFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticSearchJsonDirectoryFixture2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticSearchJsonDirectoryFixtureTest extends TestCase
{
    /** @var Client|MockObject */
    private $client;

    public function testLoad(): void
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('bulk')
            ->withConsecutive(
                [
                    [
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
                    ],
                ],
                [
                    [
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
                    ],
                ]
            );

        (new ElasticSearchJsonDirectoryFixture1())->load($this->client, 'my_index');
    }

    public function testLoadWithInvalidJson(): void
    {
        $this->client
            ->expects($this->once())
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
            ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Error decoding JSON file: "%s"',
                realpath(__DIR__ . '/../TestFixtures/Elasticsearch/ElasticSearchJsonDirectoryFixture2/docs2.json')
            )
        );

        (new ElasticSearchJsonDirectoryFixture2())->load($this->client, 'my_index');
    }

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
    }
}
