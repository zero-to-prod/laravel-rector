<?php

namespace App\Http\Controllers;

readonly class UserIndexController
{
    public function __invoke(): string
    {
        return 'index';
    }
}
