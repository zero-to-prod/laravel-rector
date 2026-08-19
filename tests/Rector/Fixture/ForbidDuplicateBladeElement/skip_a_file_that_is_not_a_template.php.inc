<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\ForbidDuplicateBladeElement;

class Head
{
    public function render(): string
    {
        return '<title>One</title><title>Two</title>';
    }
}
