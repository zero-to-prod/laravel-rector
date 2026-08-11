<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

readonly class MixedGroup
{
    public const NAME = 'name', COUNT = 1;
}
