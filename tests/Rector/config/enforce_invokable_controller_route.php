<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\EnforceInvokableControllerRouteRector;

return RectorConfig::configure()
    ->withRules([EnforceInvokableControllerRouteRector::class]);
