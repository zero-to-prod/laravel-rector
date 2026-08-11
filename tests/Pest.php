<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use ZeroToProd\LaravelRector\Tests\TestCase;

pest()->extend(TestCase::class)->in(__DIR__);
pest()->tia()->locally();

/**
 * Runs the given work with no application booted, which is how a rule finds the world, and
 * puts the one this suite runs on back however the work ends.
 *
 * @param  Closure(): void  $work
 */
function withoutApplication(Closure $work): void
{
    $Application = Facade::getFacadeApplication();
    $Container = Container::getInstance();

    Facade::clearResolvedInstances();
    Facade::setFacadeApplication(null);

    try {
        $work();
    } finally {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($Application);
        Container::setInstance($Container);
    }
}
