<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use PhpParser\Node;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * A comment a project has decided against is a comment no file should carry: a note left for
 * nobody, a slur, a ticket number the tracker no longer knows, a name a rewrite retired.
 *
 * Which phrases those are is yours to name, with `phrases`. A phrase written as a delimited
 * pattern, such as `/fixme/i`, is matched as a regular expression; every other phrase is
 * matched as text, without regard to case. A pattern PCRE cannot compile is refused as the
 * rule is configured, naming the reason, rather than quietly matching nothing.
 *
 * There is nothing to rewrite a phrase to, so every comment carrying one is reported as an
 * error naming the phrase, the comment and the line it is written on.
 *
 * The phrases are read from the file's comment tokens, in a line comment, a hash comment or a
 * docblock, so one written inside a string or a heredoc is not a violation.
 *
 * Configured with `leave_todo`, the rule reports nothing at all: the note it would leave is
 * the comment it just found.
 */
final class ForbidCommentPhraseRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo {
        configure as private configureLeavesTodo;
    }

    public const string PHRASES = 'phrases';

    /**
     * A phrase reading as a delimited pattern, such as `/fixme/i`, rather than as text.
     */
    private const string PATTERN = '#^/.*/[imsxuADSUXJn]*$#';

    /** @var list<string> Configured phrases, as text or as a delimited pattern */
    private array $phrases = [];

    /** @param  mixed[]  $configuration */
    public function configure(array $configuration): void
    {
        $this->configureLeavesTodo($configuration);

        $phrases = $configuration[self::PHRASES] ?? [];

        $this->phrases = array_values(array_filter(is_array($phrases) ? $phrases : [], is_string(...)));

        foreach ($this->phrases as $phrase) {
            if (! $this->readsAsPattern($phrase)) {
                continue;
            }

            $error = $this->error($phrase);

            if ($error === null) {
                continue;
            }

            throw new InvalidConfigurationException(sprintf(
                'The phrase "%s" reads as a pattern, and PCRE cannot compile it: %s. Fix the pattern, or escape its delimiters to forbid it as text.',
                $phrase,
                $error,
            ));
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Comments must not carry a configured phrase', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    // FIXME the empty case
                    return $items[0];
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    return $items[0] ?? null;
                    CODE_SAMPLE,
                [self::PHRASES => ['/fixme/i']],
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
     * Reading the comment tokens, rather than the comments a node carries, keeps a phrase from
     * being found in a string and keeps a comment attached to no node at all from being missed.
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
                foreach ($this->phrases as $phrase) {
                    if (! $this->matches($phrase, $line)) {
                        continue;
                    }

                    throw new ShouldNotHappenException(
                        sprintf(
                            'Comment carries the forbidden phrase %s: "%s". Rewrite the comment, or drop it. See %s:%d',
                            $phrase,
                            trim(ltrim(trim($line), '*#/')),
                            $this->file->getFilePath(),
                            $Token->line + $offset,
                        )
                    );
                }
            }
        }

        return null;
    }

    /** Whether the line carries the phrase, as a regular expression or as text of any casing. */
    private function matches(string $phrase, string $line): bool
    {
        if ($this->readsAsPattern($phrase)) {
            return preg_match($phrase, $line) === 1;
        }

        return str_contains(strtolower($line), strtolower($phrase));
    }

    /** Whether the phrase is written as a delimited pattern rather than as text. */
    private function readsAsPattern(string $phrase): bool
    {
        return preg_match(self::PATTERN, $phrase) === 1;
    }

    /**
     * The reason PCRE cannot compile the pattern, or null when it compiles.
     *
     * A pattern PCRE rejects is a warning rather than an exception, and the reason it gives is
     * the one worth repeating, so it is read from the warning the compile raises.
     */
    private function error(string $pattern): ?string
    {
        $error = null;

        set_error_handler(function (int $severity, string $message) use (&$error): bool {
            $error = $message;

            return true;
        });

        preg_match($pattern, '');

        restore_error_handler();

        return $error;
    }
}
