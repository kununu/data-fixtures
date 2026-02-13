<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\PropertyCreateMockToCreateStubRector;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;

return RectorConfig::configure()
    ->withPhpSets(php84: true)
    ->withAttributesSets(phpunit: true)
    ->withComposerBased(phpunit: true)
    ->withRules([
        FinalizeTestCaseClassRector::class,
        PreferPHPUnitSelfCallRector::class,
    ])
    ->withSkip([
        __DIR__ . '/rector-ci.php',
        AddOverrideAttributeToOverriddenMethodsRector::class,
        PropertyCreateMockToCreateStubRector::class => [
            __DIR__ . '/tests/Executor/AbstractExecutorTestCase.php',
            __DIR__ . '/tests/Purger/AbstractConnectionPurgerTestCase.php',
        ],
        StringClassNameToClassConstantRector::class => [
            __DIR__ . '/src/Tools/DoctrineDbal/Version.php',
        ],
    ])
    ->withImportNames();
