<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\ForbidClassUsageRector;
use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy;

return RectorConfig::configure()
    ->withConfiguredRule(ForbidClassUsageRector::class, [
        ForbidClassUsageRector::CLASSES => [Legacy::class],
    ]);
