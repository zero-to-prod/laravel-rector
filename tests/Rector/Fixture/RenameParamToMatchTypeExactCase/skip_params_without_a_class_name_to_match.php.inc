<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCase;

class Basket
{
    public function __construct(private Apple $pie) {}

    public function addAll(Apple ...$apples) {}

    public function label(string $name) {}

    public function weigh($anything) {}
}
