<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableController;

abstract class Controller
{
    public function authorize(string $ability): bool
    {
        return true;
    }
}
