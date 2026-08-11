<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use ZeroToProd\LaravelRector\Internal\RoutedControllers;

it('reports the class each route maps to', function (): void {
    Route::get('/user', 'App\Actions\UserShow@__invoke')->name('user.show');

    $RoutedController = collect(RoutedControllers::all())->firstOrFail();

    expect($RoutedController->class)->toBe('App\Actions\UserShow')
        ->and($RoutedController->action)->toBe('__invoke')
        ->and($RoutedController->methods)->toBe(['GET', 'HEAD'])
        ->and($RoutedController->uri)->toBe('user')
        ->and($RoutedController->name)->toBe('user.show')
        ->and($RoutedController->route())->toBe('GET /user');
});

it('reports the method a route maps to when the action is not invokable', function (): void {
    Route::post('/user', 'App\Http\Controllers\UserController@store');

    $RoutedController = collect(RoutedControllers::all())->firstOrFail();

    expect($RoutedController->class)->toBe('App\Http\Controllers\UserController')
        ->and($RoutedController->action)->toBe('store')
        ->and($RoutedController->name)->toBeNull()
        ->and($RoutedController->route())->toBe('POST /user');
});

it('reports every route, including two reaching one class', function (): void {
    Route::get('/user', 'App\Actions\UserShow@__invoke');
    Route::get('/profile', 'App\Actions\UserShow@__invoke');

    expect(RoutedControllers::all())->toHaveCount(2);
});

it('skips a route mapping to a closure', function (): void {
    Route::get('/health', fn (): string => 'ok');

    expect(RoutedControllers::all())->toBeEmpty();
});
