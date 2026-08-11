<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\AddReadonlyToClassWithTraitRector;
use ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel;

return RectorConfig::configure()
    ->withConfiguredRule(AddReadonlyToClassWithTraitRector::class, [
        AddReadonlyToClassWithTraitRector::TRAITS => [DataModel::class],
        AddReadonlyToClassWithTraitRector::LEAVE_TODO => true,
    ]);
