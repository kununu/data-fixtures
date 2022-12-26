<?php
declare(strict_types=1);

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
    ],
];
