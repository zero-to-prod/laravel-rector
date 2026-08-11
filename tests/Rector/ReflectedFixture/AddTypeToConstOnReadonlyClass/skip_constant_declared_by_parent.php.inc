<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

use ZeroToProd\LaravelRector\Tests\Rector\Source\HasName;

readonly class InheritsName implements HasName
{
    public const NAME = 'other';
}
