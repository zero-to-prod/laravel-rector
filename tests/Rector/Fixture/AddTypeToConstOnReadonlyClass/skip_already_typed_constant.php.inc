<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

readonly class AlreadyTyped
{
    public const string NAME = 'name';
}
