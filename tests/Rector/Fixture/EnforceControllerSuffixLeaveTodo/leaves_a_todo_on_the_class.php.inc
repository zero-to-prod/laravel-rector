<?php

namespace App\Actions;

readonly class UserShow
{
    public function __invoke(string $id): string
    {
        return $id;
    }
}
-----
<?php

namespace App\Actions;

// TODO: Class "UserShow" is the controller for route "GET /user" and does not end in Controller. Rename it UserShowController.
readonly class UserShow
{
    public function __invoke(string $id): string
    {
        return $id;
    }
}
