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
 * A controller is one readonly action: it declares `__invoke`, nothing else public, and
 * nothing about itself it can change.
 *
 * A class whose name ends in `Controller` is held to it. Every other public method is an
 * action hiding in a class that already has one, and there is nothing to rewrite it to —
 * where it belongs is a controller of its own, named for what it does. So each one is
 * reported as an error naming the file and line, as is a controller declaring no public
 * `__invoke` at all, and one not declared readonly: an action holds the dependencies it was
 * handed and changes nothing about itself between being constructed and being called.
 *
 * A constructor, a static `middleware()` declared for Laravel's `HasMiddleware`, and any
 * method that is not public are left alone: none of them is reachable as a route action.
 * An abstract class is left alone too — a base controller routes to nothing.
 *
 * Configured with `require_readonly` set to false, how a controller is declared stops being
 * the rule's business and only the invokable half is enforced.
 */
final class EnforceInvokableControllerRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo {
        configure as private configureLeavesTodo;
    }

    public const string REQUIRE_READONLY = 'require_readonly';

    /**
     * Public methods a controller may declare, none of which is reachable as a route action.
     */
    private const array PERMITTED_METHODS = [
        '__invoke',
        '__construct',
        'middleware',
    ];

    private const string NO_INVOKE = 'Controller declares no public __invoke. Controllers are invokable: name its action __invoke.';

    private const string NOT_READONLY = 'Controller is not readonly. An action holds the dependencies it was handed and changes nothing about itself: declare it readonly.';

    private bool $requireReadonly = true;

    /** @param  mixed[]  $configuration */
    public function configure(array $configuration): void
    {
        $this->configureLeavesTodo($configuration);

        $this->requireReadonly = (bool) ($configuration[self::REQUIRE_READONLY] ?? true);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Controllers must be readonly and invokable, declaring __invoke and no other public method', [
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
                    readonly class UserShowController
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
                    readonly class UserController
                    {
                        public function __invoke(): View
                        {
                            return view('user.index');
                        }

                        public function show(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    readonly class UserController
                    {
                        public function __invoke(): View
                        {
                            return view('user.index');
                        }

                        // TODO: Controller declares public method "show". Controllers are invokable: move it to a controller of its own, named __invoke.
                        public function show(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
                [self::LEAVE_TODO => true],
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
                    class UserShowController
                    {
                        public function __invoke(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
                [self::REQUIRE_READONLY => false],
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

            $hasChanged = $this->report($ClassMethod, $violation) || $hasChanged;
        }

        foreach ($this->classViolations($node) as $violation) {
            $hasChanged = $this->report($node, $violation) || $hasChanged;
        }

        return $hasChanged ? $node : null;
    }

    /**
     * What the class itself gets wrong, rather than one of its methods.
     *
     * @return list<string>
     */
    private function classViolations(Class_ $Class_): array
    {
        $violations = [];

        if ($Class_->getMethod('__invoke')?->isPublic() !== true) {
            $violations[] = self::NO_INVOKE;
        }

        if ($this->requireReadonly && ! $Class_->isReadonly()) {
            $violations[] = self::NOT_READONLY;
        }

        return $violations;
    }

    /**
     * Whether the node was given a comment naming the violation, having been configured to
     * leave one rather than report the violation as an error.
     *
     * @throws ShouldNotHappenException
     */
    private function report(Node $Node, string $violation): bool
    {
        if (! $this->leavesTodo()) {
            throw new ShouldNotHappenException(sprintf('%s See %s', $violation, $this->describeLocation($Node)));
        }

        return $this->annotate($Node, $violation) instanceof Node;
    }

    private function describeLocation(Node $Node): string
    {
        return sprintf('%s:%d', $this->file->getFilePath(), $Node->getStartLine());
    }
}
