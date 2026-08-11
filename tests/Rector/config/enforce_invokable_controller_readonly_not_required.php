<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\EnforceInvokableControllerRector;

return RectorConfig::configure()
    ->withConfiguredRule(EnforceInvokableControllerRector::class, [
        EnforceInvokableControllerRector::REQUIRE_READONLY => false,
    ]);
