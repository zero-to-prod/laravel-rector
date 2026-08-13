<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\CollapseSingleLineDocblockRector;

return RectorConfig::configure()
    ->withRules([CollapseSingleLineDocblockRector::class]);
