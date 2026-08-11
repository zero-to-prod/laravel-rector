<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Violation\EnforceInvokableControllerRoute;

use Illuminate\Support\Facades\Route;

Route::resource('users', UserController::class);
