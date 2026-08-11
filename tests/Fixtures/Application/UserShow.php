<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Fixtures\Application;

/**
 * The class the fixture application's one controller route maps to. The router resolves an
 * invokable action against the class itself, so it has to exist to be routed to.
 */
readonly class UserShow
{
    public function __invoke(): string
    {
        return 'user';
    }
}
