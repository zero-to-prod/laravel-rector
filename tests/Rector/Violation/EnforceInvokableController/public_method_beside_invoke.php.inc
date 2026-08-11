<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Violation\EnforceInvokableController;

class UserController
{
    public function __invoke(): string
    {
        return 'index';
    }

    public function show(string $id): string
    {
        return $id;
    }
}
