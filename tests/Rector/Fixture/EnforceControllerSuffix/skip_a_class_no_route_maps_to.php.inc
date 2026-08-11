<?php

namespace App\Http\Controllers;

readonly class UserPresenter
{
    public function __invoke(string $id): string
    {
        return $id;
    }
}
