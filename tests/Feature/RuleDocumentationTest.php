<?php

declare(strict_types=1);

use ZeroToProd\LaravelRector\Internal\RuleDocumentation;

function renderRuleDocs(): string
{
    return RuleDocumentation::render(
        dirname(__DIR__).'/Fixtures/RuleDocs',
        'ZeroToProd\\LaravelRector\\Tests\\Fixtures\\RuleDocs',
    );
}

it('indexes every rule with the description it carries', function (): void {
    expect(renderRuleDocs())
        ->toContain("## Rules\n\n- [`AddedLinesRule`](#addedlinesrule) — Adds a trailing line")
        ->toContain('- [`RemovedLinesRule`](#removedlinesrule) — Drops a trailing line')
        ->toContain('- [`UndocumentedRule`](#undocumentedrule) — Swaps a line for another');
});

it('shows every rule being registered in rector.php', function (): void {
    expect(renderRuleDocs())
        ->toContain('use ZeroToProd\LaravelRector\Tests\Fixtures\RuleDocs\AddedLinesRule;')
        ->toContain("    ->withRules([\n        AddedLinesRule::class,\n        RemovedLinesRule::class,\n        UndocumentedRule::class,\n    ]);");
});

it('omits a class that documents no rule', function (): void {
    expect(renderRuleDocs())->not->toContain('NotARule');
});

it('renders the class doc comment as prose, dropping its tags', function (): void {
    expect(renderRuleDocs())
        ->toContain("### `AddedLinesRule`\n\nAdds the line the sample is missing.\n\nA second paragraph, so the prose is more than one.\n\n```diff")
        ->not->toContain('@see');
});

it('renders a rule that carries no doc comment as its heading and sample', function (): void {
    expect(renderRuleDocs())->toContain("### `UndocumentedRule`\n\n```diff");
});

it('renders a sample as a diff, keeping the lines the rule leaves alone', function (): void {
    expect(renderRuleDocs())
        ->toContain("```diff\n first();\n+second();\n```")
        ->toContain("```diff\n first();\n-second();\n```")
        ->toContain("```diff\n-old();\n+added();\n keep();\n```");
});

it('writes the section between the markers, leaving the rest of the readme alone', function (): void {
    $readme = "# Title\n\n<!-- rules:start -->\n\nstale\n\n<!-- rules:end -->\n\n## After\n";

    expect(RuleDocumentation::apply($readme, '## Rules'))
        ->toBe("# Title\n\n<!-- rules:start -->\n\n## Rules\n\n<!-- rules:end -->\n\n## After\n");
});

it('refuses a readme with nowhere to write', function (): void {
    expect(fn (): string => RuleDocumentation::apply('# Title', '## Rules'))
        ->toThrow(RuntimeException::class, 'markers to write between');
});

it('keeps its own readme saying what the rules say', function (): void {
    $readme = (string) file_get_contents(RuleDocumentation::path());

    expect(RuleDocumentation::apply($readme, RuleDocumentation::section()))->toBe($readme);
})->note('composer docs rewrites it');
