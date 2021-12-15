<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Kununu\DataFixtures\FixtureInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface HttpClientFixtureInterface extends FixtureInterface
{
    public function load(HttpClientInterface $httpClient): void;
}
