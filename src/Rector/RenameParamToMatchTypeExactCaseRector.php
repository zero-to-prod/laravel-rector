<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\ClassReflection;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\Exception\PoorDocumentationException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * A parameter typed with a class is named after that class, in the class's own casing.
 *
 * Methods that override a parent or interface declaration are left alone: their parameter
 * names are part of a contract this rule has no business rewriting.
 */
final class RenameParamToMatchTypeExactCaseRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo;

    public function __construct(
        private readonly ReflectionResolver $reflectionResolver,
    ) {}

    /** @throws PoorDocumentationException */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename param to match class type hint exactly (PascalCase)', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run(Apple $pie)
                        {
                            $food = $pie;
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run(Apple $Apple)
                        {
                            $food = $Apple;
                        }
                    }
                    CODE_SAMPLE,
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run(Apple $pie)
                        {
                            $food = $pie;
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        // TODO: rename $pie to $Apple, after its type
                        public function run(Apple $pie)
                        {
                            $food = $pie;
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
        return [ClassMethod::class, Function_::class];
    }

    /** @param  ClassMethod|Function_  $node */
    public function refactor(Node $node): ?Node
    {
        // Skip methods that override a parent/interface — renaming params would break the contract
        if ($node instanceof ClassMethod && $this->isOverrideMethod($node)) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->params as $param) {
            if ($param->variadic) {
                continue;
            }

            if ($param->type === null) {
                continue;
            }

            if ($param->flags !== 0) {
                continue;
            }

            $expectedName = $this->resolveExpectedName($param);

            if ($expectedName === null) {
                continue;
            }

            $currentName = $this->getName($param->var);

            if ($currentName === null || $currentName === $expectedName) {
                continue;
            }

            if ($this->hasConflictingParam($node, $expectedName, $param)) {
                continue;
            }

            if ($this->leavesTodo()) {
                $hasChanged = $this->annotate($node, sprintf('rename $%s to $%s, after its type', $currentName, $expectedName)) instanceof Node || $hasChanged;

                continue;
            }

            $param->var = new Variable($expectedName);
            $this->renameVariableInBody($node, $currentName, $expectedName);
            $hasChanged = true;
        }

        return $hasChanged ? $node : null;
    }

    /**
     * Only a class type has a casing to match: scalars, unions and intersections are skipped.
     */
    private function resolveExpectedName(Param $Param): ?string
    {
        return $Param->type instanceof Name ? $Param->type->getLast() : null;
    }

    private function hasConflictingParam(FunctionLike $FunctionLike, string $expectedName, Param $Param): bool
    {
        foreach ($FunctionLike->getParams() as $param) {
            if ($param === $Param) {
                continue;
            }

            if ($this->getName($param->var) === $expectedName) {
                return true;
            }
        }

        return false;
    }

    private function isOverrideMethod(ClassMethod $ClassMethod): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($ClassMethod);

        // @codeCoverageIgnoreStart
        // A method always carries the scope of the class it is declared in
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        $methodName = $this->getName($ClassMethod);

        foreach ($classReflection->getAncestors() as $ancestor) {
            if ($ancestor->getName() === $classReflection->getName()) {
                continue;
            }

            if ($ancestor->hasNativeMethod($methodName)) {
                return true;
            }
        }

        return false;
    }

    private function renameVariableInBody(FunctionLike $FunctionLike, string $oldName, string $newName): void
    {
        $stmts = $FunctionLike->getStmts();

        if ($stmts === null) {
            return;
        }

        $this->traverseNodesWithCallable($stmts, function (Node $node) use ($oldName, $newName): ?Variable {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->isName($node, $oldName)) {
                return null;
            }

            $node->name = $newName;

            return $node;
        });
    }
}
