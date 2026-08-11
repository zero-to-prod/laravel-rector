<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddReadonlyToClassWithTrait;

use ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel;
use ZeroToProd\LaravelRector\Tests\Rector\Source\Timestamps;

class User
{
    use DataModel;
    use Timestamps;

    public string $name;
}

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\AddReadonlyToClassWithTrait;

use ZeroToProd\LaravelRector\Tests\Rector\Source\DataModel;
use ZeroToProd\LaravelRector\Tests\Rector\Source\Timestamps;

readonly class User
{
    use DataModel;
    use Timestamps;

    public string $name;
}

?>
