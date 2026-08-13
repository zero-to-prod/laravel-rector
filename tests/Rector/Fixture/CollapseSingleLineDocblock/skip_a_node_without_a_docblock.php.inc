<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\CollapseSingleLineDocblock;

class WithoutADocblock
{
    // A line comment says nothing about a docblock
    public function handle(): void {}
}
