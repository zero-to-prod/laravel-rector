<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

readonly class Computed
{
    public const SUM = 1 + 1;
}
