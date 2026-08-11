<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddReadonlyToClassWithTrait;

use ZeroToProd\LaravelRector\Tests\Rector\Source\BaseKitchen;
use ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel;

class Kitchen extends BaseKitchen
{
    use DataModel;

    public string $name;
}
