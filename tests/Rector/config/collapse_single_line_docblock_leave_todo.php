<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\CollapseSingleLineDocblockRector;

return RectorConfig::configure()
    ->withConfiguredRule(CollapseSingleLineDocblockRector::class, [
        CollapseSingleLineDocblockRector::LEAVE_TODO => true,
    ]);
