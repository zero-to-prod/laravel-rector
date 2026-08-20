<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Rector\Exception\ShouldNotHappenException;

final class ForbidBladeAttributeValueRectorTest extends RectorTestCase
{
    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideViolations')]
    public function test_reports_a_template_writing_a_forbidden_value_in_a_configured_attribute(string $fixture, string $expectedMessage): void
    {
        expect(fn () => $this->doTestFile(__DIR__.'/Violation/ForbidBladeAttributeValue/'.$fixture))
            ->toThrow(ShouldNotHappenException::class, $expectedMessage);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/ForbidBladeAttributeValue');
    }

    /** @return Iterator<array{string, string}> */
    public static function provideViolations(): Iterator
    {
        yield ['a_hard_coded_form_action.blade.php.inc', 'The action attribute is written as "/users", and the pattern #^/# is forbidden in it'];
        yield ['a_hard_coded_path.blade.php.inc', 'The href attribute is written as "/dashboard", and the pattern #^/# is forbidden in it'];
        yield ['a_hard_coded_path_in_no_quotes.blade.php.inc', 'The href attribute is written as "/profile", and the pattern #^/# is forbidden in it'];
        yield ['a_hard_coded_path_in_single_quotes.blade.php.inc', 'The href attribute is written as "/settings", and the pattern #^/# is forbidden in it'];
        yield ['a_hard_coded_path_on_its_own_line.blade.php.inc', 'The href attribute is written as "/reports", and the pattern #^/# is forbidden in it'];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/forbid_blade_attribute_value.php';
    }
}
