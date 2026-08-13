<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use Illuminate\Support\Facades\DB;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * A class a project has decided against is a class no file should name: a facade it is moving
 * off, a helper a rewrite replaced, a package class it no longer wants reached directly.
 *
 * Which classes those are is yours to name, with `classes`. There is nothing to rewrite a
 * forbidden class to, so the rule never changes the code: every statement naming one carries
 * a comment saying so instead, and running twice leaves one comment rather than two.
 *
 * A statement names a class however PHP lets it: an import, a parent, an interface, an
 * attribute, a type, a `new`, a static call. The name is read as resolved, so the short name
 * an import brought in and the fully qualified one are the same class. A statement nested in
 * another waits its own turn, so the comment lands on the line the name is written on.
 *
 * Configured with `leave_todo`, nothing changes: the comment is all this rule ever leaves.
 */
final class ForbidClassUsageRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo {
        configure as private configureLeavesTodo;
    }

    public const string CLASSES = 'classes';

    /** @var list<string> Configured class names, lowercased and unqualified by a leading slash */
    private array $classes = [];

    /** @param  mixed[]  $configuration */
    public function configure(array $configuration): void
    {
        $this->configureLeavesTodo($configuration);

        $classes = $configuration[self::CLASSES] ?? [];

        $this->classes = array_values(array_map(
            $this->normalize(...),
            array_filter(is_array($classes) ? $classes : [], is_string(...)),
        ));
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Statements must not name a configured class', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    $user = DB::table('users')->first();
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    // TODO: do not use Illuminate\Support\Facades\DB
                    $user = DB::table('users')->first();
                    CODE_SAMPLE,
                [self::CLASSES => [DB::class]],
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        // Every statement, so the comment lands on the one the name is written on
        return [Stmt::class];
    }

    /** @param  Stmt  $node */
    public function refactor(Node $node): ?Node
    {
        $class = $this->forbiddenClass($node);

        if ($class === null) {
            return null;
        }

        return $this->annotate($node, sprintf('do not use %s', $class));
    }

    /**
     * The forbidden class the node names, as the file resolves it, looking past the statements
     * nested in it: those arrive as statements of their own.
     */
    private function forbiddenClass(Node $Node): ?string
    {
        foreach ($Node->getSubNodeNames() as $subNodeName) {
            /** @var mixed $subNode */
            $subNode = $Node->{$subNodeName};

            /** @var mixed $child */
            foreach (is_array($subNode) ? $subNode : [$subNode] as $child) {
                if (! $child instanceof Node || $child instanceof Stmt) {
                    continue;
                }

                if ($child instanceof Name) {
                    $class = (string) $this->getName($child);

                    if (in_array($this->normalize($class), $this->classes, true)) {
                        return $class;
                    }

                    continue;
                }

                $found = $this->forbiddenClass($child);

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /** A class name as it compares: PHP names a class without regard to case or a leading slash. */
    private function normalize(string $class): string
    {
        return strtolower(ltrim($class, '\\'));
    }
}
