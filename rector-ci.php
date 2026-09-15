<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;

return RectorConfig::configure()
    ->withPhpSets()
    ->withAttributesSets(phpunit: true)
    ->withComposerBased(phpunit: true)
    ->withRules([
        FinalizeTestCaseClassRector::class,
        PreferPHPUnitSelfCallRector::class,
    ])
    ->withSkip([
        __DIR__ . '/rector-ci.php',
        StringClassNameToClassConstantRector::class => [
            __DIR__ . '/src/Tools/DoctrineDbal/Version.php',
        ],
    ])
    ->withImportNames();
