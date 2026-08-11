<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerLeaveTodo;

readonly class UserController
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
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerLeaveTodo;

readonly class UserController
{
    public function __invoke(): string
    {
        return 'index';
    }

    // TODO: Controller declares public method "show". Controllers are invokable: move it to a controller of its own, named __invoke.
    public function show(string $id): string
    {
        return $id;
    }
}
