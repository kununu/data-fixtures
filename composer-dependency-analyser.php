<?php
declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableReportingUnmatchedIgnores()
    ->ignoreErrors([ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackages(
        [
            'psr/log',
            'symfony/http-client-contracts',
        ],
        [ErrorType::SHADOW_DEPENDENCY]
    )
    ->setFileExtensions(['php']);
