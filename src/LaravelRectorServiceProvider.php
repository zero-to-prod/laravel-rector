<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\Facades\Mcp;
use Override;
use ZeroToProd\LaravelRector\Internal\Commands\InstallCommand;
use ZeroToProd\LaravelRector\Internal\Mcp\Server;

/** @internal */
class LaravelRectorServiceProvider extends ServiceProvider
{
    /** @internal */
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-rector.php', 'laravel-rector');
    }

    /** @internal */
    public function boot(): void
    {
        $this->registerMcpServer();

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/laravel-rector.php' => config_path('laravel-rector.php'),
            ], 'laravel-rector-config');
        }
    }

    private function registerMcpServer(): void
    {
        // @codeCoverageIgnoreStart
        if (! class_exists(Mcp::class)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        if (! Config::boolean('laravel-rector.mcp.enabled', true)) {
            return;
        }

        Mcp::local(Config::string('laravel-rector.mcp.handle', 'laravel-rector'), Server::class);
    }
}
