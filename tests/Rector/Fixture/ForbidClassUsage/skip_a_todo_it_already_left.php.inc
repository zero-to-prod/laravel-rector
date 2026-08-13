<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\ForbidClassUsage;

// TODO: do not use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy
use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy;

final class Refund
{
    public function issue(): void
    {
        // TODO: do not use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy
        $Legacy = Legacy::make();
    }
}
