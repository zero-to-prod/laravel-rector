<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerLeaveTodo;

// TODO: Controller declares no public __invoke. Controllers are invokable: name its action __invoke.
class UserEditController
{
    // TODO: Controller declares public method "edit". Controllers are invokable: move it to a controller of its own, named __invoke.
    public function edit(string $id): string
    {
        return $id;
    }
}
