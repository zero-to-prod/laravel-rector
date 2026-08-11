<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Rector\Exception\ShouldNotHappenException;

final class EnforceInvokableControllerRouteRectorTest extends RectorTestCase
{
    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideViolations')]
    public function test_reports_a_route_that_names_a_method(string $fixture, string $expectedMessage): void
    {
        expect(fn () => $this->doTestFile(__DIR__.'/Violation/EnforceInvokableControllerRoute/'.$fixture))->toThrow(ShouldNotHappenException::class, $expectedMessage);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/EnforceInvokableControllerRoute');
    }

    /** @return Iterator<array{string, string}> */
    public static function provideViolations(): Iterator
    {
        yield ['array_action_naming_a_method.php.inc', 'Route action maps to method "show". Controllers are invokable: pass the controller class itself.'];
        yield ['string_action_naming_a_method.php.inc', 'Route action maps to method "UserController@show". Controllers are invokable: pass the controller class itself.'];
        yield ['method_mapping_registrar.php.inc', "Route::resource() maps a controller's methods. Register one route per invokable controller instead."];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/enforce_invokable_controller_route.php';
    }
}
