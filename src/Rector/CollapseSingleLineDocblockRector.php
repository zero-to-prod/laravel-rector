<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * A docblock saying one thing is written on one line, so the three lines it used to take
 * become the one line it needs.
 *
 * A docblock saying more than one thing is left as it is written: the moment a second line
 * carries anything at all, the shape of the block is the reader's own, and collapsing it
 * would be a rewrite rather than a tidy.
 *
 * The line is the docblock's only content, whichever line it was written on, so both a block
 * opening on its own line and one opening on the content's line collapse the same way.
 */
final class CollapseSingleLineDocblockRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Write a docblock saying one thing on one line', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    /**
                     * @throws ReflectionException
                     */
                    public function handle(): void
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    /** @throws ReflectionException */
                    public function handle(): void
                    CODE_SAMPLE,
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    /**
                     * @throws ReflectionException
                     */
                    public function handle(): void
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    /**
                     * @throws ReflectionException
                     */
                    // TODO: write this docblock on one line
                    public function handle(): void
                    CODE_SAMPLE,
                [self::LEAVE_TODO => true],
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        // Every node a docblock can be written above, including a promoted property
        return [Stmt::class, Param::class];
    }

    public function refactor(Node $node): ?Node
    {
        $Doc = $node->getDocComment();

        if (! $Doc instanceof Doc) {
            return null;
        }

        $collapsed = $this->collapse($Doc->getText());

        if ($collapsed === null) {
            return null;
        }

        if ($this->leavesTodo()) {
            return $this->annotate($node, 'write this docblock on one line');
        }

        $node->setDocComment(new Doc($collapsed));

        return $node;
    }

    /**
     * The docblock written on one line, or null when it already is written on one or says
     * more than one thing.
     */
    private function collapse(string $text): ?string
    {
        $lines = explode("\n", trim($text));

        if (count($lines) === 1) {
            return null;
        }

        $content = [];

        // The text between the opening and closing markers, a line at a time
        foreach (explode("\n", substr(trim($text), 3, -2)) as $line) {
            $line = trim(ltrim(trim($line), '*'));

            if ($line === '') {
                continue;
            }

            $content[] = $line;
        }

        if (count($content) !== 1) {
            return null;
        }

        return sprintf('/** %s */', $content[0]);
    }
}
