<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Violation\EnforceInvokableControllerReadonlyNotRequired;

class UserIndexController
{
    public function __construct(private Users $Users) {}
}
