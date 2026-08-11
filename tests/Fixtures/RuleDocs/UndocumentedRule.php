<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Fixtures\RuleDocs;

use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UndocumentedRule implements DocumentedRuleInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Swaps a line for another', [
            new CodeSample("old();\nkeep();", "added();\nkeep();"),
        ]);
    }
}
