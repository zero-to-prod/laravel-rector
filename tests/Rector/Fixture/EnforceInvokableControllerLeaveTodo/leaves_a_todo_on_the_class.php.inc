<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerLeaveTodo;

class UserIndexController
{
    public function handle(): string
    {
        return 'index';
    }
}
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerLeaveTodo;

// TODO: Controller declares no public __invoke. Controllers are invokable: name its action __invoke.
class UserIndexController
{
    // TODO: Controller declares public method "handle". Controllers are invokable: move it to a controller of its own, named __invoke.
    public function handle(): string
    {
        return 'index';
    }
}
