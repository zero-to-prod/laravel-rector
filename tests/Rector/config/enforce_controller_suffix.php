<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\EnforceControllerSuffixRector;

return RectorConfig::configure()
    ->withRules([EnforceControllerSuffixRector::class]);
