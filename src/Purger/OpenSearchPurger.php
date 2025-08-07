<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use OpenSearch\Client;
use stdClass;

final readonly class OpenSearchPurger implements PurgerInterface
{
    public function __construct(private Client $client, private string $indexName)
    {
    }

    public function purge(): void
    {
        // We need to refresh before, otherwise we may hit version conflicts when messing up with the same document.
        $this->client->indices()->refresh(['index' => $this->indexName]);

        $this->client->deleteByQuery(
            [
                'index'     => $this->indexName,
                'body'      => [
                    'query' => [
                        'match_all' => new stdClass(),
                    ],
                ],
                'conflicts' => 'proceed',
            ]
        );

        $this->client->indices()->refresh(['index' => $this->indexName]);
    }
}
