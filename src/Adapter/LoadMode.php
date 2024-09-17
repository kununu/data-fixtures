<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

enum LoadMode: string
{
    case Include = 'include';
    case Load = 'load';
    case LoadJson = 'loadJson';
}
