<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Rector\Exception\ShouldNotHappenException;

final class ForbidDuplicateBladeElementRectorTest extends RectorTestCase
{
    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideViolations')]
    public function test_reports_a_template_writing_a_configured_element_twice(string $fixture, string $expectedMessage): void
    {
        expect(fn () => $this->doTestFile(__DIR__.'/Violation/ForbidDuplicateBladeElement/'.$fixture))
            ->toThrow(ShouldNotHappenException::class, $expectedMessage);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/ForbidDuplicateBladeElement');
    }

    /** @return Iterator<array{string, string}> */
    public static function provideViolations(): Iterator
    {
        yield ['a_component_opened_twice.blade.php.inc', 'Template writes the <x-layout> element 2 times, and it is written once: on lines 1, 5'];
        yield ['two_titles.blade.php.inc', 'Template writes the <title> element 2 times, and it is written once: on lines 2, 3'];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/forbid_duplicate_blade_element.php';
    }
}
