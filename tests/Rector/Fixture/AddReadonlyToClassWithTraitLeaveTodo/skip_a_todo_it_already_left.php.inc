<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddReadonlyToClassWithTraitLeaveTodo;

use ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel;

// TODO: declare this class readonly: it uses ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel
class AlreadyAnnotated
{
    use DataModel;

    public string $name;
}
