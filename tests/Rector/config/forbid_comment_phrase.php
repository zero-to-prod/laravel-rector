<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\ForbidCommentPhraseRector;

return RectorConfig::configure()
    ->withConfiguredRule(ForbidCommentPhraseRector::class, [
        ForbidCommentPhraseRector::PHRASES => ['hack', '/fixme(\s|$)/i'],
    ]);
