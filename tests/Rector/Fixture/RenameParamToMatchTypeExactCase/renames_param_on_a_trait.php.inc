<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCase;

trait Peeler
{
    public function peel(Apple $pie)
    {
        return $pie;
    }
}
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCase;

trait Peeler
{
    public function peel(Apple $Apple)
    {
        return $Apple;
    }
}
