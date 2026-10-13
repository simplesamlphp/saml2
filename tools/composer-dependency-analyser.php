<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    // ext-soap is under 'suggested' and is not required
    ->ignoreErrorsOnExtension('ext-soap', [ErrorType::SHADOW_DEPENDENCY])

    // simplesamlphp/simplesamlphp is a circular dependency that we ignore for now
    ->ignoreErrorsOnPackage('simplesamlphp/simplesamlphp', [ErrorType::DEV_DEPENDENCY_IN_PROD]);
