<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddReadonlyToClassWithTrait;

use ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel;

class Counted
{
    use DataModel;

    public static int $count;
}
