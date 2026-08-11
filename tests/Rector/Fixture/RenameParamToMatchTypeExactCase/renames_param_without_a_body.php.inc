<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCase;

interface Eater
{
    public function eat(Apple $pie);
}

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCase;

interface Eater
{
    public function eat(Apple $Apple);
}

?>
