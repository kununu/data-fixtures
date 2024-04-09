<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Elasticsearch\Client;
use stdClass;

final class ElasticsearchPurger implements PurgerInterface
{
    public function __construct(private readonly Client $elasticSearch, private readonly string $indexName)
    {
    }

    public function purge(): void
    {
        // We need to refresh before, otherwise we may hit version conflicts when messing up with the same document.
        $this->elasticSearch->indices()->refresh(['index' => $this->indexName]);

        $this->elasticSearch->deleteByQuery(
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

        $this->elasticSearch->indices()->refresh(['index' => $this->indexName]);
    }
}
