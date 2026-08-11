<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerReadonlyNotRequired;

class UserShowController
{
    public function __construct(private Users $Users) {}

    public function __invoke(string $id): string
    {
        return $this->Users->find($id);
    }
}
