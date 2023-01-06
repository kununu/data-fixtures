<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Elasticsearch\Client;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticSearchArrayDirectoryFixture1;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticSearchArrayDirectoryFixtureTest extends TestCase
{
    public function testLoad(): void
    {
        /** @var Client|MockObject $client */
        $client = $this->createMock(Client::class);

        $client
            ->expects($this->exactly(2))
            ->method('bulk')
            ->withConsecutive(
                [
                    [
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
                    ],
                ],
                [
                    [
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
                    ],
                ]
            );

        (new ElasticSearchArrayDirectoryFixture1())->load($client, 'my_index');
    }
}
