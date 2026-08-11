<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector\Source;

use DateTimeImmutable;

class BaseKitchen
{
    public function bake(DateTimeImmutable $DateTimeImmutable): DateTimeImmutable
    {
        return $DateTimeImmutable;
    }
}
