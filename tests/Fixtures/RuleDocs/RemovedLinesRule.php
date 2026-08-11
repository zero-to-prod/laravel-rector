<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Fixtures\RuleDocs;

use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/** Drops the line the sample does not need. */
final class RemovedLinesRule implements DocumentedRuleInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Drops a trailing line', [
            new CodeSample("first();\nsecond();", 'first();'),
        ]);
    }
}
