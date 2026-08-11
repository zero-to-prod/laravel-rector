<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddReadonlyToClassWithTrait;

use ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel;

abstract class BaseModel
{
    use DataModel;

    public string $name;
}
