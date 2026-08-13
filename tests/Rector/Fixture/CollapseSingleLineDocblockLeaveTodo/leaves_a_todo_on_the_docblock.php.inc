<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\CollapseSingleLineDocblockLeaveTodo;

class SaysOneThing
{
    /**
     * @throws \ReflectionException
     */
    public function handle(): void {}
}

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\CollapseSingleLineDocblockLeaveTodo;

class SaysOneThing
{
    /**
     * @throws \ReflectionException
     */
    // TODO: write this docblock on one line
    public function handle(): void {}
}

?>
