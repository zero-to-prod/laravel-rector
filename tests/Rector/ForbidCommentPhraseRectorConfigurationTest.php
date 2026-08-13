<?php

declare(strict_types=1);

use Rector\Exception\Configuration\InvalidConfigurationException;
use ZeroToProd\LaravelRector\Rector\ForbidCommentPhraseRector;

/** @param  list<string>  $phrases */
function configureForbidCommentPhrase(array $phrases): callable
{
    return function () use ($phrases): void {
        new ForbidCommentPhraseRector()->configure([ForbidCommentPhraseRector::PHRASES => $phrases]);
    };
}

it('refuses a phrase that reads as a pattern PCRE cannot compile', function (): void {
    expect(configureForbidCommentPhrase(['/fixme(/i']))->toThrow(
        InvalidConfigurationException::class,
        'The phrase "/fixme(/i" reads as a pattern, and PCRE cannot compile it:',
    );
});

it('accepts a phrase read as text, whatever it would mean as a pattern', function (): void {
    expect(configureForbidCommentPhrase(['a hack(', '/fixme/i']))->not->toThrow(InvalidConfigurationException::class);
});
