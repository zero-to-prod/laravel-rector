<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\ForbidClassUsage;

use DateTimeImmutable;

final class Receipt
{
    public function issue(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-01-01');
    }
}
