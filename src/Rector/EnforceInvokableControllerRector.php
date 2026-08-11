<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * A controller is one action: it declares `__invoke` and nothing else public.
 *
 * A class whose name ends in `Controller` is held to it. Every other public method is an
 * action hiding in a class that already has one, and there is nothing to rewrite it to —
 * where it belongs is a controller of its own, named for what it does. So each one is
 * reported as an error naming the file and line, as is a controller declaring no public
 * `__invoke` at all.
 *
 * A constructor, a static `middleware()` declared for Laravel's `HasMiddleware`, and any
 * method that is not public are left alone: none of them is reachable as a route action.
 * An abstract class is left alone too — a base controller routes to nothing.
 */
final class EnforceInvokableControllerRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo;

    /**
     * Public methods a controller may declare, none of which is reachable as a route action.
     */
    private const array PERMITTED_METHODS = [
        '__invoke',
        '__construct',
        'middleware',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Controllers must be invokable, declaring __invoke and no other public method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    class UserController
                    {
                        public function show(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    class UserShowController
                    {
                        public function __invoke(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    class UserController
                    {
                        public function show(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    class UserController
                    {
                        // TODO: Controller declares public method "show". Controllers are invokable: move it to a controller of its own, named __invoke.
                        public function show(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
                [self::LEAVE_TODO => true],
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param  Class_  $node
     *
     * @throws ShouldNotHappenException
     */
    public function refactor(Node $node): ?Class_
    {
        // An anonymous class has no name to end in Controller, and a base controller routes to nothing
        if (! str_ends_with($node->name?->toString() ?? '', 'Controller') || $node->isAbstract()) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->getMethods() as $ClassMethod) {
            if (! $ClassMethod->isPublic() || in_array($ClassMethod->name->toString(), self::PERMITTED_METHODS, true)) {
                continue;
            }

            $violation = sprintf(
                'Controller declares public method "%s". Controllers are invokable: move it to a controller of its own, named __invoke.',
                $ClassMethod->name->toString(),
            );

            if (! $this->leavesTodo()) {
                throw new ShouldNotHappenException(sprintf('%s See %s', $violation, $this->describeLocation($ClassMethod)));
            }

            $hasChanged = $this->annotate($ClassMethod, $violation) instanceof Node || $hasChanged;
        }

        if ($node->getMethod('__invoke')?->isPublic() === true) {
            return $hasChanged ? $node : null;
        }

        $violation = 'Controller declares no public __invoke. Controllers are invokable: name its action __invoke.';

        if (! $this->leavesTodo()) {
            throw new ShouldNotHappenException(sprintf('%s See %s', $violation, $this->describeLocation($node)));
        }

        return $this->annotate($node, $violation) instanceof Node || $hasChanged ? $node : null;
    }

    private function describeLocation(Node $Node): string
    {
        return sprintf('%s:%d', $this->file->getFilePath(), $Node->getStartLine());
    }
}
