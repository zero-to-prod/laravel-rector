<?php

namespace App\Actions;

readonly class UserShow
{
    public function __invoke(string $id): string
    {
        return $id;
    }
}
