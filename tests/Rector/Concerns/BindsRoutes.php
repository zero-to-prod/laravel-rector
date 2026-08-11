<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector\Concerns;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;

/**
 * Stands an application's router up for the rules that read routes.
 *
 * Rector runs outside an application and boots one to ask what its routes map to. A router
 * the facades already resolve is the application a test hands it instead, so no fixture
 * here is answered by booting anything.
 */
trait BindsRoutes
{
    protected function bindRoutes(): void
    {
        $Application = new Application;
        $Router = new Router(new Dispatcher($Application), $Application);

        // The fixture classes are never loaded, so each action names its method rather than
        // being an invokable class string, which the router resolves against the class
        $Router->get('/user', 'App\Actions\UserShow@__invoke');
        $Router->get('/user/{id}/edit', 'App\Actions\UserEdit@__invoke');
        $Router->get('/users', 'App\Http\Controllers\UserIndexController@__invoke');
        $Router->get('/health', fn (): string => 'ok');

        $Application->instance('router', $Router);

        Facade::setFacadeApplication($Application);
    }

    protected function unbindRoutes(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
    }
}
