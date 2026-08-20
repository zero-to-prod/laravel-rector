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
 * An attribute value written by hand is a value a refactoring cannot follow: a path typed into
 * an `href`, a route spelled out in a form's `action`, each of them a link still pointing where
 * the application no longer answers.
 *
 * Which values those are is yours to name, with `attributes`: a pattern the value must not
 * match, keyed by the attribute it is forbidden in. A pattern PCRE cannot compile is refused as
 * the rule is configured, naming the reason, rather than quietly matching nothing.
 *
 * An attribute is matched the way HTML reads its name: without regard to case, and only where
 * the whole name is written, so `href` is not found in `data-href`, in `:href` or in
 * `x-bind:href` — an attribute bound to an expression is already an expression. The value is
 * read however it is written, in double quotes, in single quotes or in neither, and the pattern
 * is matched against the value alone.
 *
 * Only Blade templates are read, the files named `*.blade.php`. A value written inside a Blade
 * comment or an HTML comment is not written on the page, so it is not read.
 *
 * There is nothing to rewrite a forbidden value to, so a template writing one is reported as an
 * error naming the attribute, the value, the pattern that forbids it and the line it is written
 * on.
 *
 * Configured with `leave_todo`, the rule reports nothing: a template renders what it says, and
 * a note left in one is a note the page would carry.
 */
final class ForbidBladeAttributeValueRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo {
        configure as private configureLeavesTodo;
    }

    public const string ATTRIBUTES = 'attributes';

    /** The files a Blade template is written in, and the only ones this rule reads. */
    private const string TEMPLATE = '.blade.php';

    /** @var array<string, string> Forbidden value patterns, keyed by attribute name as a name compares */
    private array $attributes = [];

    /** @param  mixed[]  $configuration */
    public function configure(array $configuration): void
    {
        $this->configureLeavesTodo($configuration);

        /** @var mixed $attributes */
        $attributes = $configuration[self::ATTRIBUTES] ?? [];

        $this->attributes = [];

        /** @var mixed $pattern */
        foreach (is_array($attributes) ? $attributes : [] as $attribute => $pattern) {
            if (! is_string($attribute) || ! is_string($pattern)) {
                continue;
            }

            $name = $this->normalize($attribute);

            if ($name === '') {
                continue;
            }

            $error = $this->error($pattern);

            if ($error !== null) {
                throw new InvalidConfigurationException(sprintf(
                    'The pattern "%s" forbidden in the %s attribute is one PCRE cannot compile: %s. Fix the pattern.',
                    $pattern,
                    $name,
                    $error,
                ));
            }

            $this->attributes[$name] = $pattern;
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Blade templates must not write a forbidden value in a configured attribute', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    <a href="/home">Home</a>
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    <a href="{{ route('home') }}">Home</a>
                    CODE_SAMPLE,
                [self::ATTRIBUTES => ['href' => '#^/#']],
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        // The node every file arrives as, so the template is read once, as it is written
        return [FileNode::class];
    }

    /**
     * Reading the file as it is written, rather than the nodes it parses to, keeps a template
     * that is markup to PHP from being a file with nothing in it to read.
     *
     * @throws ShouldNotHappenException
     */
    public function refactor(Node $node): null
    {
        // Configured with `leave_todo`, the rule has nothing to leave: a note would render
        if ($this->leavesTodo()) {
            return null;
        }

        $path = $this->file->getFilePath();

        if (! str_ends_with(strtolower($path), self::TEMPLATE)) {
            return null;
        }

        $template = $this->uncommented($this->file->getFileContent());

        foreach ($this->attributes as $attribute => $pattern) {
            $violation = $this->violation($attribute, $pattern, $template);

            if ($violation === null) {
                continue;
            }

            [$line, $value] = $violation;

            throw new ShouldNotHappenException(
                sprintf(
                    'The %s attribute is written as "%s", and the pattern %s is forbidden in it. Write the value as an expression a refactoring can follow. See %s:%d',
                    $attribute,
                    $value,
                    $pattern,
                    $path,
                    $line,
                )
            );
        }

        return null;
    }

    /**
     * The line and the value of the first forbidden value written in the attribute, or null when
     * the template writes none.
     *
     * @return array{int, string}|null
     */
    private function violation(string $attribute, string $pattern, string $template): ?array
    {
        // A name is the whole name, so the one a prefix or a binding colon carries is another
        // attribute; the value is whichever way it is quoted, read as the one group it captures
        $matcher = sprintf(
            '#(?<![-:\w@.])%s\s*=\s*(?|"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+))#i',
            preg_quote($attribute, '#'),
        );

        preg_match_all($matcher, $template, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        /** @var list<array<int, array{string, int}>> $matches */
        foreach ($matches as $match) {
            if (preg_match($pattern, $match[1][0]) !== 1) {
                continue;
            }

            return [substr_count($template, "\n", 0, $match[0][1]) + 1, $match[1][0]];
        }

        return null;
    }

    /**
     * The template with its comments blanked out, keeping the newlines they carry so a line
     * is still the line it is written on.
     */
    private function uncommented(string $template): string
    {
        return (string) preg_replace_callback(
            '#{{--.*?--}}|<!--.*?-->#s',
            static fn (array $match): string => str_repeat("\n", substr_count((string) $match[0], "\n")),
            $template,
        );
    }

    /** An attribute name as it compares: HTML names an attribute without regard to case. */
    private function normalize(string $attribute): string
    {
        return strtolower(trim($attribute));
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
