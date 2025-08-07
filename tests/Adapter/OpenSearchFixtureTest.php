<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Kununu\DataFixtures\Tests\TestFixtures\OpenSearchFixture3;
use OpenSearch\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class OpenSearchFixtureTest extends TestCase
{
    private MockObject&Client $client;

    public function testLoad(): void
    {
        $this->client
            ->expects($this->once())
            ->method('bulk')
            ->with([
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

        (new OpenSearchFixture3())->load($this->client, 'my_index');
    }

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
    }
}
