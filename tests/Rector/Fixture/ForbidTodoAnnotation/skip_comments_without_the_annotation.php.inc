<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\ForbidTodoAnnotation;

/**
 * A note that tracks nothing is still a note.
 *
 * @see https://example.test
 */
class Notes
{
    // A plain comment
    public const string TODO = 'the @todo in this string is not a comment';

    public function heredoc(): string
    {
        return <<<'TEXT'
            @todo neither is this one
            TEXT;
    }
}
