<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector\Source;

use DateTimeImmutable;

trait Timestamps
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-01-01');
    }
}
