<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Rector\Exception\ShouldNotHappenException;

final class EnforceInvokableControllerRectorTest extends RectorTestCase
{
    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideViolations')]
    public function test_reports_a_controller_that_is_not_invokable(string $fixture, string $expectedMessage): void
    {
        expect(fn () => $this->doTestFile(__DIR__.'/Violation/EnforceInvokableController/'.$fixture))->toThrow(ShouldNotHappenException::class, $expectedMessage);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/EnforceInvokableController');
    }

    /** @return Iterator<array{string, string}> */
    public static function provideViolations(): Iterator
    {
        yield ['public_method_beside_invoke.php.inc', 'Controller declares public method "show". Controllers are invokable: move it to a controller of its own, named __invoke.'];
        yield ['invoke_that_is_not_public.php.inc', 'Controller declares no public __invoke. Controllers are invokable: name its action __invoke.'];
        yield ['not_readonly.php.inc', 'Controller is not readonly. An action holds the dependencies it was handed and changes nothing about itself: declare it readonly.'];

        // The controller is not readonly either, and the action it is missing is reported first
        yield ['no_invoke_at_all.php.inc', 'Controller declares no public __invoke. Controllers are invokable: name its action __invoke.'];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/enforce_invokable_controller.php';
    }
}
