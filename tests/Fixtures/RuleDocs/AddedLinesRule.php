<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Fixtures\RuleDocs;

use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Adds the line the sample is missing.
 *
 * A second paragraph, so the prose is more than one.
 *
 * @see RemovedLinesRule
 */
final class AddedLinesRule implements DocumentedRuleInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds a trailing line', [
            new CodeSample('first();', "first();\nsecond();"),
        ]);
    }
}
