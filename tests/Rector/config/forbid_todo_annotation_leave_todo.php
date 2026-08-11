<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\ForbidTodoAnnotationRector;

return RectorConfig::configure()
    ->withConfiguredRule(ForbidTodoAnnotationRector::class, [
        ForbidTodoAnnotationRector::LEAVE_TODO => true,
    ]);
