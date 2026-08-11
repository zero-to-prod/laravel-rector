<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\RenameParamToMatchTypeExactCase;

use DateTimeImmutable;
use ZeroToProd\LaravelRector\Tests\Rector\Source\BaseKitchen;

class ChildKitchen extends BaseKitchen
{
    public function bake(DateTimeImmutable $moment): DateTimeImmutable
    {
        return $moment;
    }
}
