<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Violation\EnforceInvokableController;

class UserEditController
{
    protected function __invoke(string $id): string
    {
        return $id;
    }
}
