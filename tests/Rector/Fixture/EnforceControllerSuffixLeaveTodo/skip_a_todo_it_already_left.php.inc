<?php

namespace App\Actions;

// TODO: Class "UserEdit" is the controller for route "GET /user/{id}/edit" and does not end in Controller. Rename it UserEditController.
readonly class UserEdit
{
    public function __invoke(string $id): string
    {
        return $id;
    }
}
