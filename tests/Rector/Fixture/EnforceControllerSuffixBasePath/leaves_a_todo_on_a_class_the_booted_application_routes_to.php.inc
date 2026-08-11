<?php

namespace ZeroToProd\LaravelRector\Tests\Fixtures\Application;

readonly class UserShow
{
    public function __invoke(): string
    {
        return 'user';
    }
}
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Fixtures\Application;

// TODO: Class "UserShow" is the controller for route "GET /user" and does not end in Controller. Rename it UserShowController.
readonly class UserShow
{
    public function __invoke(): string
    {
        return 'user';
    }
}
