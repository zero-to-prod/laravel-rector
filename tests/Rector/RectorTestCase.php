<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Rector decides it is running under PHPUnit by this constant, and only then rethrows a
 * rule's exception and stops caching the reflection it builds per fixture. The pest binary
 * never defines it, and defining it before the runner boots makes PHPUnit preload its own
 * class map over Pest's overrides — so it is defined here, once the runner is up.
 */
abstract class RectorTestCase extends AbstractRectorTestCase
{
    protected function setUp(): void
    {
        if (! defined('PHPUNIT_COMPOSER_INSTALL')) {
            define('PHPUNIT_COMPOSER_INSTALL', dirname(__DIR__, 2).'/vendor/autoload.php');
        }

        parent::setUp();
    }
}
