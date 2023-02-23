<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Elasticsearch\Client;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticSearchArrayDirectoryFixture1;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticSearchArrayDirectoryFixtureTest extends TestCase
{
    private MockObject|Client $client;

    public function testLoad(): void
    {
        $bulk1 = [
            'type' => '_doc',
            'body' => [
                [
                    'index' => [
                        '_index' => 'my_index',
                        '_id'    => 1,
                        '_type'  => '_doc',
                    ],
                ],
                [
                    'id'         => 1,
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
                        '_id'    => 2,
                        '_type'  => '_doc',
                    ],
                ],
                [
                    'id'         => 2,
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
            'type' => '_doc',
            'body' => [
                [
                    'index' => [
                        '_index' => 'my_index',
                        '_id'    => 3,
                        '_type'  => '_doc',
                    ],
                ],
                [
                    'id'         => 3,
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
            ->expects($this->exactly(2))
            ->method('bulk')
            ->with(
                $this->callback(
                    fn (array $bulk): bool => ($bulk == $bulk1 || $bulk == $bulk2)
                )
            );

        (new ElasticSearchArrayDirectoryFixture1())->load($this->client, 'my_index');
    }

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
    }
}
