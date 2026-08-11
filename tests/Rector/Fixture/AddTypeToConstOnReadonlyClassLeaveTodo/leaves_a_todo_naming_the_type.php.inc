<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClassLeaveTodo;

readonly class Untyped
{
    public const NAME = 'name';
}
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClassLeaveTodo;

readonly class Untyped
{
    // TODO: type this constant as string
    public const NAME = 'name';
}
