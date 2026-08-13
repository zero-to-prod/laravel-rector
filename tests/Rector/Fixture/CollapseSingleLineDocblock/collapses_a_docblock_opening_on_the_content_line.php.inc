<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\CollapseSingleLineDocblock;

class OpensOnTheContentLine
{
    /** @throws \ReflectionException
     */
    public function handle(): void {}
}

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\CollapseSingleLineDocblock;

class OpensOnTheContentLine
{
    /** @throws \ReflectionException */
    public function handle(): void {}
}

?>
