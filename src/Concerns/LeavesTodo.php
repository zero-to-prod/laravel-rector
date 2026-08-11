<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Concerns;

use PhpParser\Comment;
use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * Makes a rule configurable with `leave_todo`.
 *
 * Configured with it, the rule stops changing the code and stops reporting an error. It
 * leaves a comment naming the violation where it found it instead, so the change stays a
 * decision for whoever reads it. Running twice leaves one comment, not two.
 */
trait LeavesTodo
{
    public const string LEAVE_TODO = 'leave_todo';

    private const string TODO = '// TODO: ';

    private bool $leaveTodo = false;

    /** @param  mixed[]  $configuration */
    public function configure(array $configuration): void
    {
        $this->leaveTodo = (bool) ($configuration[self::LEAVE_TODO] ?? false);
    }

    private function leavesTodo(): bool
    {
        return $this->leaveTodo;
    }

    /**
     * The node carrying a comment naming the violation, or null when it already carries it.
     *
     * @template TNode of Node
     *
     * @param  TNode  $Node
     * @return TNode|null
     */
    private function annotate(Node $Node, string $violation): ?Node
    {
        $todo = self::TODO.$violation;
        $comments = $Node->getComments();

        foreach ($comments as $Comment) {
            if ($Comment->getText() === $todo) {
                return null;
            }
        }

        $comments[] = new Comment($todo);
        $Node->setAttribute(AttributeKey::COMMENTS, $comments);

        return $Node;
    }
}
