<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector\Source;

trait DataModel
{
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
