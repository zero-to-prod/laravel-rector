<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Internal\Mcp\Tools;

use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use ZeroToProd\LaravelRector\Internal\RuleDocumentation;

/**
 * Every rule the package ships, as the rule itself describes it.
 *
 * @internal
 */
#[IsReadOnly]
#[IsIdempotent]
class Rules extends Tool
{
    protected string $description = 'Lists every Rector rule this package ships: what it does, the code it rewrites and how to register it.';

    public function handle(): Response
    {
        return Response::text(RuleDocumentation::section()."\n");
    }
}
