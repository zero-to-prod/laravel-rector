<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use PhpParser\Node;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;

/**
 * An element a page is allowed one of is an element a template must write once: a second
 * `<title>`, a second `<h1>`, a second `<x-layout>`, each of them a page saying two things
 * where the browser reads one.
 *
 * Which elements those are is yours to name, with `elements`. A name is written as the tag
 * is, `title` or `x-layout`, and is matched the way HTML reads a tag name: without regard to
 * case, and only where the whole name is written, so `<title>` is not found in `<titlebar>`.
 *
 * Only Blade templates are read, the files named `*.blade.php`, and only their opening tags
 * count: a closing tag is the same element, written again. An element written inside a Blade
 * comment or an HTML comment is not written on the page, so it is not counted.
 *
 * There is nothing to rewrite a second element to, so a template writing one is reported as
 * an error naming the element, the number of times it is written and the lines it is written
 * on.
 *
 * Configured with `leave_todo`, the rule reports nothing: a template renders what it says,
 * and a note left in one is a note the page would carry.
 */
final class ForbidDuplicateBladeElementRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo {
        configure as private configureLeavesTodo;
    }

    public const string ELEMENTS = 'elements';

    /** The files a Blade template is written in, and the only ones this rule reads. */
    private const string TEMPLATE = '.blade.php';

    /** @var list<string> Configured element names, as a tag name compares */
    private array $elements = [];

    /** @param  mixed[]  $configuration */
    public function configure(array $configuration): void
    {
        $this->configureLeavesTodo($configuration);

        $elements = $configuration[self::ELEMENTS] ?? [];

        $this->elements = array_values(array_filter(
            array_map(
                $this->normalize(...),
                array_filter(is_array($elements) ? $elements : [], is_string(...)),
            ),
            static fn (string $element): bool => $element !== '',
        ));
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Blade templates must write a configured element once', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    <title>@yield('title')</title>
                    <title>Dashboard</title>
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    <title>@yield('title', 'Dashboard')</title>
                    CODE_SAMPLE,
                [self::ELEMENTS => ['title']],
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

        foreach ($this->elements as $element) {
            $lines = $this->lines($element, $template);

            if (count($lines) < 2) {
                continue;
            }

            throw new ShouldNotHappenException(
                sprintf(
                    'Template writes the <%s> element %d times, and it is written once: on lines %s. Drop the ones the page does not need. See %s',
                    $element,
                    count($lines),
                    implode(', ', $lines),
                    $path,
                )
            );
        }

        return null;
    }

    /**
     * The lines the element's opening tag is written on, in the order it is written.
     *
     * @return list<int>
     */
    private function lines(string $element, string $template): array
    {
        // A tag name ends where the tag does: at a space, at a slash, or at the closing angle
        $pattern = sprintf('#<%s(?=[\s/>])#i', preg_quote($element, '#'));

        preg_match_all($pattern, $template, $matches, PREG_OFFSET_CAPTURE);

        return array_map(
            static fn (array $match): int => substr_count($template, "\n", 0, (int) $match[1]) + 1,
            $matches[0],
        );
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

    /** An element name as it compares: HTML names a tag without regard to case or its angles. */
    private function normalize(string $element): string
    {
        return strtolower(trim($element, " \t\n\r<>/"));
    }
}
