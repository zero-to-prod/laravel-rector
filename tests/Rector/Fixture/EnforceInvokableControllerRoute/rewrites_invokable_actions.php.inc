<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerRoute;

use Illuminate\Support\Facades\Route;

Route::get('/user', [UserShowController::class, '__invoke']);
Route::match(['get', 'post'], '/user', [UserShowController::class, '__invoke']);
Route::fallback([UserShowController::class, '__invoke']);
Route::prefix('admin')->middleware('auth')->get('/user', [UserShowController::class, '__invoke']);

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableControllerRoute;

use Illuminate\Support\Facades\Route;

Route::get('/user', UserShowController::class);
Route::match(['get', 'post'], '/user', UserShowController::class);
Route::fallback(UserShowController::class);
Route::prefix('admin')->middleware('auth')->get('/user', UserShowController::class);

?>
