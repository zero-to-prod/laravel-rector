<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Violation\EnforceInvokableController;

readonly class UserEditController
{
    protected function __invoke(string $id): string
    {
        return $id;
    }
}
