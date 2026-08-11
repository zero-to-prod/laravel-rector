<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use ZeroToProd\LaravelRector\Tests\Fixtures\Application\UserShow;

Route::get('/user', UserShow::class);

// A route mapping to no class at all
Route::get('/health', fn (): string => 'ok');
