<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

/**
 * The smallest application that still answers what its routes map to, standing in for the
 * one a rule boots out of the directory Rector was run in.
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: dirname(__DIR__).'/routes/web.php')
    ->create();
