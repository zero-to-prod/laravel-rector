<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Internal;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * The class each of the application's routes maps to, as the router itself reports it.
 *
 * Reading the router is all this does: whatever booted the application — an artisan
 * command, a test, or BootedApplication on a rule's behalf — has already happened by the
 * time it is asked. A route mapping to a closure maps to no class, and is skipped.
 *
 * @internal
 */
final class RoutedControllers
{
    /** @return list<RoutedController> */
    public static function all(): array
    {
        $controllers = [];

        foreach (Route::getRoutes()->getRoutes() as $Route) {
            $class = $Route->getControllerClass();

            if ($class === null) {
                continue;
            }

            $controllers[] = new RoutedController(
                ltrim($class, '\\'),
                Str::parseCallback($Route->getActionName(), '__invoke')[1] ?? '__invoke',
                array_values(array_map(strval(...), $Route->methods())),
                $Route->uri(),
                $Route->getName(),
            );
        }

        return $controllers;
    }
}
