<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\ForbidCommentPhrase;

/**
 * A phrase the configuration never named.
 *
 * @see https://example.test
 */
class Notes
{
    // A plain comment, and a fixmelike word the pattern does not match
    public const string HACK = 'the hack in this string is not a comment';

    public function heredoc(): string
    {
        return <<<'TEXT'
            FIXME neither is this one
            TEXT;
    }
}
