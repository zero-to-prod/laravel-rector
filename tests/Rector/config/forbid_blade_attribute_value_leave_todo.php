<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\ForbidBladeAttributeValueRector;

return RectorConfig::configure()
    ->withConfiguredRule(ForbidBladeAttributeValueRector::class, [
        ForbidBladeAttributeValueRector::ATTRIBUTES => ['href' => '#^/#'],
        ForbidBladeAttributeValueRector::LEAVE_TODO => true,
    ]);
