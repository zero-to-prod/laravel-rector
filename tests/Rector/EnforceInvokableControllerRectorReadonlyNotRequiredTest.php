<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Rector\Exception\ShouldNotHappenException;

final class EnforceInvokableControllerRectorReadonlyNotRequiredTest extends RectorTestCase
{
    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideViolations')]
    public function test_reports_what_is_left_of_the_rule(string $fixture, string $expectedMessage): void
    {
        expect(fn () => $this->doTestFile(__DIR__.'/Violation/EnforceInvokableControllerReadonlyNotRequired/'.$fixture))->toThrow(ShouldNotHappenException::class, $expectedMessage);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/EnforceInvokableControllerReadonlyNotRequired');
    }

    /** @return Iterator<array{string, string}> */
    public static function provideViolations(): Iterator
    {
        // Only how the controller is declared stops being the rule's business
        yield ['still_reports_a_missing_invoke.php.inc', 'Controller declares no public __invoke. Controllers are invokable: name its action __invoke.'];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/enforce_invokable_controller_readonly_not_required.php';
    }
}
