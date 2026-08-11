<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClassLeaveTodo;

readonly class AlreadyAnnotated
{
    // TODO: type this constant as string
    public const NAME = 'name';
}
