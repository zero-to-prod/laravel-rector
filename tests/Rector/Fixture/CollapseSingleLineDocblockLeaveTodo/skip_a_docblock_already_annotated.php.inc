<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\CollapseSingleLineDocblockLeaveTodo;

class AlreadyAnnotated
{
    /**
     * @throws \ReflectionException
     */
    // TODO: write this docblock on one line
    public function handle(): void {}
}
