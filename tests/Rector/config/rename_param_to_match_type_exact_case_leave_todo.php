<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\RenameParamToMatchTypeExactCaseRector;

return RectorConfig::configure()
    ->withConfiguredRule(RenameParamToMatchTypeExactCaseRector::class, [
        RenameParamToMatchTypeExactCaseRector::LEAVE_TODO => true,
    ]);
