<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\ForbidDuplicateBladeElementRector;

return RectorConfig::configure()
    ->withConfiguredRule(ForbidDuplicateBladeElementRector::class, [
        ForbidDuplicateBladeElementRector::ELEMENTS => ['title', '<x-layout>', ''],
    ]);
