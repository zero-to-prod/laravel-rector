<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddReadonlyToClassWithTrait;

use ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel;

class Defaulted
{
    use DataModel;

    public string $name = 'name';
}
