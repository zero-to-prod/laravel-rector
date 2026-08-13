<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\ForbidClassUsage;

use DateTimeImmutable;
use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy;

final class Order
{
    public function make(DateTimeImmutable $DateTimeImmutable): Legacy
    {
        return Legacy::make();
    }
}

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\ForbidClassUsage;

use DateTimeImmutable;
// TODO: do not use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy
use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy;

final class Order
{
    // TODO: do not use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy
    public function make(DateTimeImmutable $DateTimeImmutable): Legacy
    {
        // TODO: do not use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy
        return Legacy::make();
    }
}

?>
