<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableController;

class UserPresenter
{
    public function show(string $id): string
    {
        return $id;
    }
}

new class
{
    public function show(string $id): string
    {
        return $id;
    }
};
