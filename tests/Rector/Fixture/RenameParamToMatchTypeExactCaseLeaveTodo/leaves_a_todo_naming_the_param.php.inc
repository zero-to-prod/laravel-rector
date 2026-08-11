<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCaseLeaveTodo;

class Kitchen
{
    public function bake(Apple $pie)
    {
        return $pie;
    }
}
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCaseLeaveTodo;

class Kitchen
{
    // TODO: rename $pie to $Apple, after its type
    public function bake(Apple $pie)
    {
        return $pie;
    }
}
