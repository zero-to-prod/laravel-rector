<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerRoute;

use Illuminate\Support\Facades\Route;

Route::get('/closure', function () {
    return 1;
});
Route::get('/arrow', fn () => 1);
Route::get('/invokable', UserShowController::class);
Route::get('/string-without-method', 'UserShowController');
Route::get('/one-element-array', [UserShowController::class]);
Route::get('/dynamic-method', [UserShowController::class, $method]);
Route::get('/string-controller', ['UserShowController', '__invoke']);
Route::get('/no-action');
Route::middleware('auth');
Route::{'g'.'et'}('/dynamic-verb', [UserShowController::class, 'show']);
$router->get('/router', [UserShowController::class, 'show']);
Registrar::get('/not-the-route-facade', [UserShowController::class, 'show']);
