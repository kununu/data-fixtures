<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;

return RectorConfig::configure()
    ->withPhpSets(php83: true)
    ->withAttributesSets(phpunit: true)
    ->withComposerBased(phpunit: true)
    ->withRules([
        FinalizeTestCaseClassRector::class,
        PreferPHPUnitSelfCallRector::class,
    ])
    ->withSkip([
        __DIR__ . '/rector-ci.php',
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withImportNames();
