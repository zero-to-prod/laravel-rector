<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use Illuminate\Support\Facades\Route;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * Controllers are invokable: a route maps to a class, never to a method on one.
 *
 * `[Controller::class, '__invoke']` is the same route written the long way, so it is
 * rewritten to `Controller::class`. Every other action that names a method — an array
 * callable, an `@` string, `Route::resource()`, `Route::controller()` — has no invokable
 * equivalent to rewrite it to and is reported as an error instead.
 */
final class EnforceInvokableControllerRouteRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo;

    /**
     * Route registration methods, mapped to the argument position holding the action.
     */
    private const array ACTION_ARGUMENT_POSITIONS = [
        'get' => 1,
        'post' => 1,
        'put' => 1,
        'patch' => 1,
        'delete' => 1,
        'options' => 1,
        'any' => 1,
        'match' => 2,
        'fallback' => 0,
    ];

    /**
     * The one violation the rule can rewrite, so the only one it reports as a comment alone.
     */
    private const string NAMES_INVOKE = 'Route action names __invoke. Pass the controller class itself.';

    /**
     * Route registration methods that map many methods of one controller by definition.
     */
    private const array METHOD_MAPPING_REGISTRARS = [
        'resource',
        'resources',
        'apiResource',
        'apiResources',
        'singleton',
        'singletons',
        'apiSingleton',
        'apiSingletons',
        'controller',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Routes must map to an invokable controller class, never to a method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    Route::get('/user', [UserShowController::class, '__invoke']);
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    Route::get('/user', UserShowController::class);
                    CODE_SAMPLE,
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    Route::get('/user', [UserShowController::class, '__invoke']);
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    // TODO: Route action names __invoke. Pass the controller class itself.
                    Route::get('/user', [UserShowController::class, '__invoke']);
                    CODE_SAMPLE,
                [self::LEAVE_TODO => true],
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        // A comment belongs to a statement, so the todo is left on the one registering the route
        return [Expression::class, StaticCall::class, MethodCall::class];
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Expression) {
            return $this->leavesTodo() ? $this->annotateRoute($node) : null;
        }

        /** @var StaticCall|MethodCall $node */
        return $this->leavesTodo() ? null : $this->refactorRoute($node);
    }

    /**
     * The statement carrying a comment for the first route it registers against this rule.
     */
    private function annotateRoute(Expression $Expression): ?Expression
    {
        $violation = null;

        $this->traverseNodesWithCallable($Expression->expr, function (Node $Node) use (&$violation): null {
            if ($violation === null && ($Node instanceof StaticCall || $Node instanceof MethodCall)) {
                $Inspection = $this->inspect($Node);
                $violation = is_array($Inspection) ? self::NAMES_INVOKE : $Inspection;
            }

            return null;
        });

        return is_string($violation) ? $this->annotate($Expression, $violation) : null;
    }

    /**
     * @throws ShouldNotHappenException
     */
    private function refactorRoute(StaticCall|MethodCall $Node): StaticCall|MethodCall|null
    {
        $Inspection = $this->inspect($Node);

        if (is_string($Inspection)) {
            throw new ShouldNotHappenException(sprintf('%s See %s', $Inspection, $this->describeLocation($Node)));
        }

        if ($Inspection === null) {
            return null;
        }

        [$Arg, $Action] = $Inspection;

        $Arg->value = $Action;

        return $Node;
    }

    /**
     * The argument to rewrite and the invokable controller to write there, a violation with no
     * rewrite to make, or null when the call registers no route this rule speaks about.
     *
     * @return array{Arg, Expr}|string|null
     */
    private function inspect(StaticCall|MethodCall $Node): array|string|null
    {
        $method_name = $this->getName($Node->name);
        if (! is_string($method_name)) {
            return null;
        }

        if (in_array($method_name, self::METHOD_MAPPING_REGISTRARS, true) && $this->isRouteRegistration($Node)) {
            return sprintf(
                'Route::%s() maps a controller\'s methods. Register one route per invokable controller instead.',
                $method_name,
            );
        }

        $position = self::ACTION_ARGUMENT_POSITIONS[$method_name] ?? null;
        if ($position === null || ! $this->isRouteRegistration($Node)) {
            return null;
        }

        $Arg = $Node->args[$position] ?? null;
        if (! $Arg instanceof Arg) {
            return null;
        }

        $Action = $this->resolveAction($Arg->value);

        if (is_string($Action)) {
            return $Action;
        }

        return $Action instanceof Expr ? [$Arg, $Action] : null;
    }

    /**
     * The invokable controller to pass instead, the violation when the action names a method,
     * or null when the action is nothing this rule speaks about.
     */
    private function resolveAction(Expr $Expr): Expr|string|null
    {
        if ($Expr instanceof Closure || $Expr instanceof ArrowFunction) {
            return null;
        }

        if ($Expr instanceof String_) {
            return str_contains($Expr->value, '@') ? $this->violation($Expr->value) : null;
        }

        if (! $Expr instanceof Array_) {
            return null;
        }

        if (count($Expr->items) !== 2) {
            return null;
        }

        [$ControllerItem, $MethodItem] = $Expr->items;

        if (! $MethodItem->value instanceof String_) {
            return null;
        }

        if ($MethodItem->value->value !== '__invoke') {
            return $this->violation($MethodItem->value->value);
        }

        if (! $ControllerItem->value instanceof ClassConstFetch) {
            return null;
        }

        return $ControllerItem->value;
    }

    /**
     * A route registration is either `Route::get(...)` on the facade or `get(...)` chained
     * onto a registrar returned by an earlier `Route::` call.
     */
    private function isRouteRegistration(StaticCall|MethodCall $Node): bool
    {
        if ($Node instanceof StaticCall) {
            return $this->isName($Node->class, Route::class);
        }

        $Root = $Node->var;
        while ($Root instanceof MethodCall) {
            $Root = $Root->var;
        }

        return $Root instanceof StaticCall && $this->isName($Root->class, Route::class);
    }

    private function violation(string $method_name): string
    {
        return sprintf(
            'Route action maps to method "%s". Controllers are invokable: pass the controller class itself.',
            $method_name,
        );
    }

    private function describeLocation(StaticCall|MethodCall $Node): string
    {
        return sprintf('%s:%d', $this->file->getFilePath(), $Node->getStartLine());
    }
}
