<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\EnforceControllerSuffixRector;

return RectorConfig::configure()
    ->withConfiguredRule(EnforceControllerSuffixRector::class, [
        EnforceControllerSuffixRector::LEAVE_TODO => true,
        EnforceControllerSuffixRector::BASE_PATH => dirname(__DIR__, 2).'/Fixtures/Application',
    ]);
