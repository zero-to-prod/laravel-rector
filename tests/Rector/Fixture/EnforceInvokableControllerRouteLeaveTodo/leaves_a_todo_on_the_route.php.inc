<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerRouteLeaveTodo;

use Illuminate\Support\Facades\Route;

Route::get('/user', [UserShowController::class, '__invoke']);
Route::get('/edit', [UserController::class, 'edit']);
Route::resource('users', UserController::class);
Route::prefix('admin')->get('/panel', [PanelController::class, '__invoke']);
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerRouteLeaveTodo;

use Illuminate\Support\Facades\Route;

// TODO: Route action names __invoke. Pass the controller class itself.
Route::get('/user', [UserShowController::class, '__invoke']);
// TODO: Route action maps to method "edit". Controllers are invokable: pass the controller class itself.
Route::get('/edit', [UserController::class, 'edit']);
// TODO: Route::resource() maps a controller's methods. Register one route per invokable controller instead.
Route::resource('users', UserController::class);
// TODO: Route action names __invoke. Pass the controller class itself.
Route::prefix('admin')->get('/panel', [PanelController::class, '__invoke']);
