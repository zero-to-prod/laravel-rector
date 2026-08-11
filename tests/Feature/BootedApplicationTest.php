<?php

declare(strict_types=1);

use Rector\Exception\ShouldNotHappenException;
use ZeroToProd\LaravelRector\Internal\BootedApplication;
use ZeroToProd\LaravelRector\Internal\RoutedControllers;
use ZeroToProd\LaravelRector\Tests\Fixtures\Application\UserShow;

it('boots the application it is pointed at when none is booted', function (): void {
    withoutApplication(function (): void {
        BootedApplication::at(dirname(__DIR__).'/Fixtures/Application');

        expect(collect(RoutedControllers::all())->firstOrFail()->class)->toBe(UserShow::class);

        // The framework installed its handlers over the runner's while booting
        restore_error_handler();
        restore_exception_handler();
    });
});

it('reports a base path holding no application to boot', function (): void {
    withoutApplication(function (): void {
        expect(fn () => BootedApplication::at(sys_get_temp_dir()))
            ->toThrow(ShouldNotHappenException::class, 'No application to boot');
    });
});

it('boots nothing when an application is booted already', function (): void {
    BootedApplication::at('/no/application/lives/here');
})->throwsNoExceptions();
