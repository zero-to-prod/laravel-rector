<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\ForbidClassUsage;

final class LegacyOrder extends \ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy
{
    public string $name = 'name';
}

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\ForbidClassUsage;

// TODO: do not use ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy
final class LegacyOrder extends \ZeroToProd\LaravelRector\Tests\Rector\Source\Legacy
{
    public string $name = 'name';
}

?>
