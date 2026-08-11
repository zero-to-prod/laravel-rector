<?php

declare(strict_types=1);

use Symplify\RuleDocGenerator\Contract\CodeSampleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use ZeroToProd\LaravelRector\Rector\AddTypeToConstOnReadonlyClassRector;
use ZeroToProd\LaravelRector\Rector\EnforceInvokableControllerRouteRector;
use ZeroToProd\LaravelRector\Rector\RenameParamToMatchTypeExactCaseRector;

it('documents what it does with a before and after sample', function (DocumentedRuleInterface $Rector): void {
    $RuleDefinition = $Rector->getRuleDefinition();

    expect($RuleDefinition->getDescription())->not->toBeEmpty()
        ->and($RuleDefinition->getCodeSamples())->not->toBeEmpty();

    foreach ($RuleDefinition->getCodeSamples() as $CodeSample) {
        expect($CodeSample)->toBeInstanceOf(CodeSampleInterface::class)
            ->and($CodeSample->getBadCode())->not->toBe($CodeSample->getGoodCode());
    }
})->with([
    // The constructor dependencies play no part in the documentation
    fn (): AddTypeToConstOnReadonlyClassRector => new ReflectionClass(AddTypeToConstOnReadonlyClassRector::class)->newInstanceWithoutConstructor(),
    fn (): EnforceInvokableControllerRouteRector => new EnforceInvokableControllerRouteRector,
    fn (): RenameParamToMatchTypeExactCaseRector => new ReflectionClass(RenameParamToMatchTypeExactCaseRector::class)->newInstanceWithoutConstructor(),
]);
