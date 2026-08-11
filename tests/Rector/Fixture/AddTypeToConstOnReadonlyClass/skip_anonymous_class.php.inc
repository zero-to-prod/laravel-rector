<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

$Anonymous = new readonly class
{
    public const NAME = 'name';
};
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

$Anonymous = new readonly class
{
    public const string NAME = 'name';
};
