<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Internal;

use FilesystemIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Symplify\RuleDocGenerator\Contract\CodeSampleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * The README's Rules section, as the rules describe themselves.
 *
 * A rule's heading is its class name, its prose is its class doc comment and its samples
 * are the ones its rule definition carries, so the document cannot drift from the code.
 *
 * @internal
 */
final class RuleDocumentation
{
    /** The generated section replaces everything between these two markers. */
    public const string START = '<!-- rules:start -->';

    public const string END = '<!-- rules:end -->';

    private const int WIDTH = 78;

    public static function path(): string
    {
        return dirname(__DIR__, 2).'/README.md';
    }

    /** The section as the package's own rules describe themselves. */
    public static function section(): string
    {
        return self::render(dirname(__DIR__).'/Rector', 'ZeroToProd\\LaravelRector\\Rector');
    }

    /**
     * @param  string  $directory  Directory holding one rule per file
     * @param  string  $namespace  PSR-4 prefix that $directory maps to
     */
    public static function render(string $directory, string $namespace): string
    {
        $rules = self::rules($directory, $namespace);

        return implode("\n\n", [
            '## Rules',
            self::index($rules),
            self::registration($rules),
            ...array_map(self::rule(...), $rules),
        ]);
    }

    /** The README carrying the given section between its markers. */
    public static function apply(string $readme, string $section): string
    {
        $start = strpos($readme, self::START);
        $end = strpos($readme, self::END);

        if ($start === false || $end === false) {
            throw new RuntimeException(sprintf('%s has no %s and %s markers to write between.', self::path(), self::START, self::END));
        }

        return substr($readme, 0, $start)
            .self::START."\n\n"
            .$section."\n\n"
            .self::END
            .substr($readme, $end + strlen(self::END));
    }

    /**
     * @return list<ReflectionClass<DocumentedRuleInterface>>
     */
    private static function rules(string $directory, string $namespace): array
    {
        $names = [];

        /** @var SplFileInfo $File */
        foreach (new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS) as $File) {
            $names[] = $namespace.'\\'.$File->getBasename('.php');
        }

        sort($names);

        $rules = [];

        foreach ($names as $name) {
            if (is_a($name, DocumentedRuleInterface::class, true)) {
                /** @var ReflectionClass<DocumentedRuleInterface> $ReflectionClass */
                $ReflectionClass = new ReflectionClass($name);
                $rules[] = $ReflectionClass;
            }
        }

        return $rules;
    }

    /** @param  list<ReflectionClass<DocumentedRuleInterface>>  $rules */
    private static function index(array $rules): string
    {
        return implode("\n", array_map(
            static fn (ReflectionClass $ReflectionClass): string => sprintf(
                '- [`%s`](#%s) — %s',
                $ReflectionClass->getShortName(),
                strtolower($ReflectionClass->getShortName()),
                self::definition($ReflectionClass)->getDescription(),
            ),
            $rules,
        ));
    }

    /** @param  list<ReflectionClass<DocumentedRuleInterface>>  $rules */
    private static function registration(array $rules): string
    {
        $imports = array_map(
            static fn (ReflectionClass $ReflectionClass): string => 'use '.$ReflectionClass->getName().';',
            $rules,
        );

        $registered = array_map(
            static fn (ReflectionClass $ReflectionClass): string => '        '.$ReflectionClass->getShortName().'::class,',
            $rules,
        );

        return implode("\n", [
            'Register the rules you want in `rector.php`:',
            '',
            '```php',
            'use Rector\Config\RectorConfig;',
            ...$imports,
            '',
            'return RectorConfig::configure()',
            '    ->withPaths([',
            "        __DIR__.'/app',",
            "        __DIR__.'/routes',",
            "        __DIR__.'/tests',",
            '    ])',
            '    ->withRules([',
            ...$registered,
            '    ]);',
            '```',
        ]);
    }

    /** @param  ReflectionClass<DocumentedRuleInterface>  $ReflectionClass */
    private static function rule(ReflectionClass $ReflectionClass): string
    {
        $samples = array_map(
            static fn (CodeSampleInterface $CodeSample): string => self::sample($ReflectionClass, $CodeSample),
            self::definition($ReflectionClass)->getCodeSamples(),
        );

        return implode("\n\n", array_filter([
            '### `'.$ReflectionClass->getShortName().'`',
            self::prose($ReflectionClass),
            ...$samples,
        ]));
    }

    /**
     * The class doc comment, its tags dropped and its paragraphs rewrapped.
     *
     * @param  ReflectionClass<DocumentedRuleInterface>  $ReflectionClass
     */
    private static function prose(ReflectionClass $ReflectionClass): string
    {
        $lines = array_map(
            static fn (string $line): string => ltrim(ltrim(trim($line), '/*')),
            explode("\n", (string) $ReflectionClass->getDocComment()),
        );

        $kept = array_filter($lines, static fn (string $line): bool => ! str_starts_with($line, '@'));

        $paragraphs = preg_split('/\n{2,}/', trim(implode("\n", $kept))) ?: [];

        return implode("\n\n", array_map(
            static fn (string $paragraph): string => wordwrap(str_replace("\n", ' ', $paragraph), self::WIDTH),
            array_filter($paragraphs),
        ));
    }

    /**
     * The sample as a diff, under the configuration it was written for.
     *
     * @param  ReflectionClass<DocumentedRuleInterface>  $ReflectionClass
     */
    private static function sample(ReflectionClass $ReflectionClass, CodeSampleInterface $CodeSample): string
    {
        $diff = "```diff\n".self::diff($CodeSample)."\n```";

        if (! $CodeSample instanceof ConfiguredCodeSample) {
            return $diff;
        }

        $options = [];

        foreach ($CodeSample->getConfiguration() as $option => $value) {
            $options[] = sprintf('    %s => %s,', var_export($option, true), var_export($value, true));
        }

        return implode("\n", [
            'Configured with:',
            '',
            '```php',
            '->withConfiguredRule('.$ReflectionClass->getShortName().'::class, [',
            ...$options,
            '])',
            '```',
            '',
            $diff,
        ]);
    }

    /**
     * The sample as a diff, so the lines the rule leaves alone read as context.
     */
    private static function diff(CodeSampleInterface $CodeSample): string
    {
        $before = explode("\n", $CodeSample->getBadCode());
        $after = explode("\n", $CodeSample->getGoodCode());

        $rows = count($before);
        $columns = count($after);

        // Longest common subsequence of the two, counted from the end backwards
        $common = array_fill(0, $rows + 1, array_fill(0, $columns + 1, 0));

        for ($row = $rows - 1; $row >= 0; $row--) {
            for ($column = $columns - 1; $column >= 0; $column--) {
                $common[$row][$column] = $before[$row] === $after[$column]
                    ? $common[$row + 1][$column + 1] + 1
                    : max($common[$row + 1][$column], $common[$row][$column + 1]);
            }
        }

        $lines = [];
        $row = 0;
        $column = 0;

        while ($row < $rows && $column < $columns) {
            if ($before[$row] === $after[$column]) {
                $lines[] = ' '.$before[$row];
                $row++;
                $column++;
            } elseif ($common[$row + 1][$column] >= $common[$row][$column + 1]) {
                $lines[] = '-'.$before[$row];
                $row++;
            } else {
                $lines[] = '+'.$after[$column];
                $column++;
            }
        }

        while ($row < $rows) {
            $lines[] = '-'.$before[$row++];
        }

        while ($column < $columns) {
            $lines[] = '+'.$after[$column++];
        }

        return implode("\n", array_map(rtrim(...), $lines));
    }

    /**
     * The constructor dependencies play no part in how a rule documents itself.
     *
     * @param  ReflectionClass<DocumentedRuleInterface>  $ReflectionClass
     */
    private static function definition(ReflectionClass $ReflectionClass): RuleDefinition
    {
        return $ReflectionClass->newInstanceWithoutConstructor()->getRuleDefinition();
    }
}
