<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCase;

class Kitchen
{
    public function bake(Apple $pie)
    {
        $food = $pie;

        return $food;
    }
}

function eat(Apple $pie)
{
    return $pie;
}

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCase;

class Kitchen
{
    public function bake(Apple $Apple)
    {
        $food = $Apple;

        return $food;
    }
}

function eat(Apple $Apple)
{
    return $Apple;
}

?>
