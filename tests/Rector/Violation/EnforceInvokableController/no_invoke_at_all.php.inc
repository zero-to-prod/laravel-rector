<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Violation\EnforceInvokableController;

class UserIndexController
{
    public function __construct(private readonly Users $Users) {}
}
