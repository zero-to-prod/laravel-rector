<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use PhpParser\Node;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * A TODO annotation is a note that the work is not finished, left where nothing tracks it.
 *
 * There is nothing to rewrite it to, so every comment carrying one is reported as an error
 * naming the file and line: finish the work, or record it where the team can see it.
 *
 * Every casing is caught, in a line comment, a hash comment or a docblock. The annotation is
 * read from the file's comment tokens, so one written inside a string or a heredoc is not a
 * violation.
 *
 * Configured with `leave_todo`, the rule reports nothing at all: the note it would leave is
 * the comment it just found.
 */
final class ForbidTodoAnnotationRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo;

    /**
     * Matched against a lowercased comment, so every casing of the annotation is caught.
     */
    private const string ANNOTATION = '@todo';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Comments must not carry a TODO annotation', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    // @TODO handle the empty case
                    return $items[0];
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    return $items[0] ?? null;
                    CODE_SAMPLE,
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        // The node every file arrives as, so the comments are read once per file
        return [FileNode::class];
    }

    /**
     * Reading the comment tokens, rather than the comments a node carries, keeps the annotation
     * from being found in a string and keeps a comment attached to no node at all from being missed.
     *
     * @throws ShouldNotHappenException
     */
    public function refactor(Node $node): null
    {
        // Configured with `leave_todo`, the rule has nothing to add: the violation is the comment
        if ($this->leavesTodo()) {
            return null;
        }

        foreach ($this->file->getOldTokens() as $Token) {
            if (! $Token->is([T_COMMENT, T_DOC_COMMENT])) {
                continue;
            }

            foreach (explode("\n", $Token->text) as $offset => $line) {
                if (! str_contains(strtolower($line), self::ANNOTATION)) {
                    continue;
                }

                throw new ShouldNotHappenException(
                    sprintf(
                        'Comment carries a TODO annotation: "%s". Finish the work, or track it outside the code. See %s:%d',
                        trim(ltrim(trim($line), '*#/')),
                        $this->file->getFilePath(),
                        $Token->line + $offset,
                    )
                );
            }
        }

        return null;
    }
}
