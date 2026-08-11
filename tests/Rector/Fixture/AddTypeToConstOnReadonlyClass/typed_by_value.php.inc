<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

readonly class TypedByValue
{
    public const NAME = 'name';
    public const COUNT = 1;
    public const RATIO = 1.5;
    public const OWED = -1;
    public const OWNED = +1;
    public const NOTHING = null;
    public const ENABLED = true;
    public const TAGS = [];
    public const GREETING = 'hello '.'world';
    public const FORMAT = \DateTimeInterface::ATOM;
}

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddTypeToConstOnReadonlyClass;

readonly class TypedByValue
{
    public const string NAME = 'name';
    public const int COUNT = 1;
    public const float RATIO = 1.5;
    public const int OWED = -1;
    public const int OWNED = +1;
    public const null NOTHING = null;
    public const bool ENABLED = true;
    public const array TAGS = [];
    public const string GREETING = 'hello '.'world';
    public const string FORMAT = \DateTimeInterface::ATOM;
}

?>
