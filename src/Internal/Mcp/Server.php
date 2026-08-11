<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Internal\Mcp;

use Laravel\Mcp\Server\Tool;
use ZeroToProd\LaravelRector\Internal\Mcp\Tools\Api;
use ZeroToProd\LaravelRector\Internal\Mcp\Tools\Install;
use ZeroToProd\LaravelRector\Internal\Mcp\Tools\Readme;
use ZeroToProd\LaravelRector\Internal\Mcp\Tools\Rules;

/** @internal */
class Server extends \Laravel\Mcp\Server
{
    protected string $name = 'Laravel Rector';

    protected string $version = '1.0.0';

    protected string $instructions = <<<'MARKDOWN'
        Documents this package for coding agents, and installs it.
        MARKDOWN;

    /** @var array<int, class-string<Tool>> */
    protected array $tools = [
        Readme::class,
        Rules::class,
        Api::class,
        Install::class,
    ];
}
