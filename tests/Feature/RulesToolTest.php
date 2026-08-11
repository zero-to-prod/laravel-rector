<?php

declare(strict_types=1);

use ZeroToProd\LaravelRector\Internal\Mcp\Server;
use ZeroToProd\LaravelRector\Internal\Mcp\Tools\Rules;
use ZeroToProd\LaravelRector\Rector\EnforceInvokableControllerRouteRector;

it('lists every rule with what it does and the code it rewrites', function (): void {
    Server::tool(Rules::class)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertName('rules')
        ->assertSee('## Rules')
        ->assertSee('### `EnforceInvokableControllerRouteRector`')
        ->assertSee('Routes must map to an invokable controller class, never to a method')
        ->assertSee("+Route::get('/user', UserShowController::class);");
});

it('shows how a rule is registered, with the options it takes', function (): void {
    Server::tool(Rules::class)
        ->assertSee('->withRules([')
        ->assertSee('EnforceInvokableControllerRouteRector::class,')
        ->assertSee("'".EnforceInvokableControllerRouteRector::LEAVE_TODO."' => true,");
});

it('describes the tool so an agent knows when to call it', function (): void {
    $tool = new Rules;

    expect($tool->name())->toBe('rules')
        ->and($tool->description())->toContain('Rector rule')
        ->and($tool->toArray()['annotations'])->toMatchArray([
            'readOnlyHint' => true,
            'idempotentHint' => true,
        ]);
});
