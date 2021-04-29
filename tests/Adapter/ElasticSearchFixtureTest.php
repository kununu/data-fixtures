<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Elasticsearch\Client;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticSearchFixture3;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticSearchFixtureTest extends TestCase
{
    public function testLoad(): void
    {
        /** @var Client|MockObject $client */
        $client = $this->createMock(Client::class);

        $client
            ->expects($this->once())
            ->method('bulk')
            ->with([
                'type' => '_doc',
                'body' => [
                    [
                        'index' => [
                            '_index' => 'my_index',
                            '_id'    => 1,
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
            ]);

        (new ElasticSearchFixture3())->load($client, 'my_index');
    }
}
