<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Internal;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Rector\Exception\ShouldNotHappenException;

/**
 * An application to ask, for the callers that run outside one.
 *
 * A Rector rule is run by Rector rather than by the application it reads, so the
 * application is booted from the `bootstrap/app.php` under the given base path. A caller
 * already inside an application — an artisan command, an MCP tool, a test — has nothing to
 * boot, and neither does the second rule to ask.
 *
 * @internal
 */
final class BootedApplication
{
    /**
     * Boots the application at the given base path, unless one is booted already.
     *
     * @throws ShouldNotHappenException
     */
    public static function at(string $basePath): void
    {
        if (Facade::getFacadeApplication() !== null) {
            return;
        }

        $bootstrap = $basePath.'/bootstrap/app.php';

        if (! is_file($bootstrap)) {
            throw new ShouldNotHappenException(sprintf(
                'No application to boot: %s does not exist. Run Rector from the application root, or configure the rule with the base path to boot from.',
                $bootstrap,
            ));
        }

        /** @var Application $Application */
        $Application = require $bootstrap;

        /** @var Kernel $Kernel */
        $Kernel = $Application->make(Kernel::class);

        $Kernel->bootstrap();
    }
}
