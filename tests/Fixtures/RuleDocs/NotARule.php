<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Fixtures\RuleDocs;

/** A class that documents no rule, and so belongs in no document. */
final class NotARule
{
    public function help(): string
    {
        return 'nothing to document';
    }
}
