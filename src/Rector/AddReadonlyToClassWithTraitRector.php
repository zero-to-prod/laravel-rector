<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * A trait can say what a class is. A class using `App\Helpers\DataModel` is a data model:
 * it is handed its values and changes none of them, so it is declared readonly.
 *
 * Which traits say so is yours to name, with `traits`. A class using one of them and not
 * declared readonly is declared readonly, and a class using none of them is left alone. The
 * trait has to be used by the class itself: a trait reached through another trait or through
 * a parent is not written in the file being read.
 *
 * A class PHP would refuse to declare readonly is left alone rather than broken: one
 * declaring a property that is static, untyped, or given a default, and one that is abstract
 * or extends another class, where the classes either side of it decide too.
 */
final class AddReadonlyToClassWithTraitRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo {
        configure as private configureLeavesTodo;
    }

    public const string TRAITS = 'traits';

    /** @var list<string> Configured trait names, lowercased and unqualified by a leading slash */
    private array $traits = [];

    /** @param  mixed[]  $configuration */
    public function configure(array $configuration): void
    {
        $this->configureLeavesTodo($configuration);

        $traits = $configuration[self::TRAITS] ?? [];

        $this->traits = array_values(array_map(
            $this->normalize(...),
            array_filter(is_array($traits) ? $traits : [], is_string(...)),
        ));
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Declare a class readonly when it uses a configured trait', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    class User
                    {
                        use DataModel;

                        public string $name;
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    readonly class User
                    {
                        use DataModel;

                        public string $name;
                    }
                    CODE_SAMPLE,
                [self::TRAITS => ['App\Helpers\DataModel']],
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    class User
                    {
                        use DataModel;

                        public string $name;
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    // TODO: declare this class readonly: it uses App\Helpers\DataModel
                    class User
                    {
                        use DataModel;

                        public string $name;
                    }
                    CODE_SAMPLE,
                [self::TRAITS => ['App\Helpers\DataModel'], self::LEAVE_TODO => true],
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
        if ($node->isReadonly() || $node->isAbstract() || $node->extends instanceof Node\Name) {
            return null;
        }

        $trait = $this->configuredTrait($node);

        if ($trait === null || $this->hasPropertyThatCannotBeReadonly($node)) {
            return null;
        }

        if ($this->leavesTodo()) {
            return $this->annotate($node, sprintf('declare this class readonly: it uses %s', $trait));
        }

        $node->flags |= Modifiers::READONLY;

        return $node;
    }

    /** The configured trait the class uses, named as the configuration names it. */
    private function configuredTrait(Class_ $Class_): ?string
    {
        foreach ($Class_->getTraitUses() as $TraitUse) {
            foreach ($TraitUse->traits as $Name) {
                $trait = (string) $this->getName($Name);

                if (in_array($this->normalize($trait), $this->traits, true)) {
                    return $trait;
                }
            }
        }

        return null;
    }

    /**
     * Whether the class declares a property a readonly class may not: a static one, an
     * untyped one, or one given a default value.
     */
    private function hasPropertyThatCannotBeReadonly(Class_ $Class_): bool
    {
        return array_any($Class_->getProperties(), static fn (Property $Property): bool => $Property->isStatic()
            || ! $Property->type instanceof Node
            || array_any($Property->props, static fn (PropertyItem $PropertyItem): bool => $PropertyItem->default instanceof Expr));
    }

    /** A trait name as it compares: PHP names a class without regard to case or a leading slash. */
    private function normalize(string $trait): string
    {
        return strtolower(ltrim($trait, '\\'));
    }
}
