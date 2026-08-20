<?php

declare(strict_types=1);

use Rector\Exception\Configuration\InvalidConfigurationException;
use ZeroToProd\LaravelRector\Rector\ForbidBladeAttributeValueRector;

/** @param  mixed[]  $attributes */
function configureForbidBladeAttributeValue(array $attributes): callable
{
    return function () use ($attributes): void {
        new ForbidBladeAttributeValueRector()->configure([ForbidBladeAttributeValueRector::ATTRIBUTES => $attributes]);
    };
}

it('refuses a pattern PCRE cannot compile', function (): void {
    expect(configureForbidBladeAttributeValue(['href' => '#^/(#']))->toThrow(
        InvalidConfigurationException::class,
        'The pattern "#^/(#" forbidden in the href attribute is one PCRE cannot compile:',
    );
});

it('reads past an attribute that names no pattern and a pattern that names no attribute', function (): void {
    expect(configureForbidBladeAttributeValue(['href', 'target' => true, 'href' => '#^/#']))
        ->not->toThrow(InvalidConfigurationException::class);
});

it('reads the attributes as nothing at all when they are not written as a list', function (): void {
    expect(configureForbidBladeAttributeValue([]))->not->toThrow(InvalidConfigurationException::class)
        ->and(fn () => new ForbidBladeAttributeValueRector()->configure([ForbidBladeAttributeValueRector::ATTRIBUTES => 'href']))
        ->not->toThrow(InvalidConfigurationException::class);
});
