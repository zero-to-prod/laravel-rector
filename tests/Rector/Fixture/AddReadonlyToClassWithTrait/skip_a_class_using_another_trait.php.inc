<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddReadonlyToClassWithTrait;

use ZeroToProd\LaravelRector\Tests\Rector\Source\Timestamps;

class Unconfigured
{
    use Timestamps;

    public string $name;
}
