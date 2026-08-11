<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

enum Status: string
{
    case Draft = 'draft';
}

readonly class WithEnumCase
{
    public const STATUS = Status::Draft;
}
