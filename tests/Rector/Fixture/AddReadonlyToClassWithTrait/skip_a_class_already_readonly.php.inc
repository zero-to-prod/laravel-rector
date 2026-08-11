<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddReadonlyToClassWithTrait;

use ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel;

readonly class AlreadyReadonly
{
    use DataModel;

    public string $name;
}
