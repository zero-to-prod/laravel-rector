<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

readonly class WithoutConstants
{
    public function __construct(public string $name) {}
}
