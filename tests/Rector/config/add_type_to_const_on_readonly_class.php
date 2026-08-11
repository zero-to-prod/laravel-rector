<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\AddTypeToConstOnReadonlyClassRector;

return RectorConfig::configure()
    ->withRules([AddTypeToConstOnReadonlyClassRector::class]);
