<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerRouteLeaveTodo;

use Illuminate\Support\Facades\Route;

Route::get('/invokable', UserShowController::class);
$total = strlen('nothing to do with routing');
