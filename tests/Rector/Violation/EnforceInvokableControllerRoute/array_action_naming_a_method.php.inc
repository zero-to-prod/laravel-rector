<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Violation\EnforceInvokableControllerRoute;

use Illuminate\Support\Facades\Route;

Route::get('/user', [UserController::class, 'show']);
