<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector\Source;

/** Stands in for a class a project has decided against, for the fixtures to name. */
class Legacy
{
    public static function make(): self
    {
        return new self;
    }
}
