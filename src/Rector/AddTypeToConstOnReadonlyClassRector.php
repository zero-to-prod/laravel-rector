<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * Constants on a readonly class carry a type, whether the class is final or not.
 *
 * A constant a parent already declares is left alone: the type it is given there is the
 * one that counts.
 */
final class AddTypeToConstOnReadonlyClassRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo;

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly StaticTypeMapper $staticTypeMapper,
    ) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add type to constants on readonly classes regardless of final', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    readonly class SomeModel
                    {
                        public const name = 'name';
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    readonly class SomeModel
                    {
                        public const string name = 'name';
                    }
                    CODE_SAMPLE,
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    readonly class SomeModel
                    {
                        public const name = 'name';
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    readonly class SomeModel
                    {
                        // TODO: type this constant as string
                        public const name = 'name';
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

    /** @param  Class_  $node */
    public function refactor(Node $node): ?Class_
    {
        if (! $node->isReadonly()) {
            return null;
        }

        $className = $this->getName($node);

        // @codeCoverageIgnoreStart
        // Every class reaching a rule is named by then: PHPStan names even an anonymous one
        if (! is_string($className)) {
            return null;
        }
        // @codeCoverageIgnoreEnd

        $classConsts = $node->getConstants();
        if ($classConsts === []) {
            return null;
        }

        $parentClassReflections = $this->getParentReflections($className);
        $hasChanged = false;

        foreach ($classConsts as $ClassConst) {
            if ($ClassConst->type !== null) {
                continue;
            }

            $valueTypes = [];

            foreach ($ClassConst->consts as $Const) {
                if ($this->isConstGuardedByParents($Const, $parentClassReflections)) {
                    continue;
                }

                $ValueType = $this->resolveValueType($Const->value);
                if ($ValueType instanceof Identifier) {
                    $valueTypes[] = $ValueType;
                }
            }

            if ($valueTypes === []) {
                continue;
            }

            $valueTypes = array_unique($valueTypes, SORT_REGULAR);
            if (count($valueTypes) !== 1) {
                continue;
            }

            $Type = current($valueTypes);

            if ($this->leavesTodo()) {
                $hasChanged = $this->annotate($ClassConst, sprintf('type this constant as %s', $Type->toString())) instanceof Node || $hasChanged;

                continue;
            }

            $ClassConst->type = $Type;
            $hasChanged = true;
        }

        return $hasChanged ? $node : null;
    }

    /** @param  ClassReflection[]  $parentClassReflections */
    private function isConstGuardedByParents(Const_ $Const_, array $parentClassReflections): bool
    {
        $constant_name = $this->getName($Const_);

        return array_any($parentClassReflections, fn (ClassReflection $ParentClassReflection): bool => $ParentClassReflection->hasConstant($constant_name));
    }

    private function resolveValueType(Expr $Expr): ?Identifier
    {
        if ($Expr instanceof UnaryPlus || $Expr instanceof UnaryMinus) {
            return $this->resolveValueType($Expr->expr);
        }

        if ($Expr instanceof String_) {
            return new Identifier('string');
        }

        if ($Expr instanceof Int_) {
            return new Identifier('int');
        }

        if ($Expr instanceof Float_) {
            return new Identifier('float');
        }

        if ($Expr instanceof ConstFetch || $Expr instanceof ClassConstFetch) {
            if ($Expr instanceof ConstFetch && $Expr->name->toLowerString() === 'null') {
                return new Identifier('null');
            }

            $Type = $this->nodeTypeResolver->getNativeType($Expr);

            $NodeType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($Type, TypeKind::PROPERTY);

            return $NodeType instanceof Identifier ? $NodeType : null;
        }

        if ($Expr instanceof Array_) {
            return new Identifier('array');
        }

        if ($Expr instanceof Concat) {
            return new Identifier('string');
        }

        return null;
    }

    /** @return ClassReflection[] */
    private function getParentReflections(string $className): array
    {
        // @codeCoverageIgnoreStart
        // The class is the one being refactored, so it is reflectable by definition
        if (! $this->reflectionProvider->hasClass($className)) {
            return [];
        }
        // @codeCoverageIgnoreEnd

        $CurrentClassReflection = $this->reflectionProvider->getClass($className);

        return array_filter(
            $CurrentClassReflection->getAncestors(),
            static fn (ClassReflection $ClassReflection): bool => $CurrentClassReflection !== $ClassReflection
        );
    }
}
