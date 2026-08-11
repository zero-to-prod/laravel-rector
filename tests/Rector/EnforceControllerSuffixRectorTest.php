<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Rector\Exception\ShouldNotHappenException;
use ZeroToProd\LaravelRector\Tests\Rector\Concerns\BindsRoutes;

final class EnforceControllerSuffixRectorTest extends RectorTestCase
{
    use BindsRoutes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bindRoutes();
    }

    protected function tearDown(): void
    {
        $this->unbindRoutes();

        parent::tearDown();
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideViolations')]
    public function test_reports_a_controller_that_is_not_named_for_one(string $fixture, string $expectedMessage): void
    {
        expect(fn () => $this->doTestFile(__DIR__.'/Violation/EnforceControllerSuffix/'.$fixture))->toThrow(ShouldNotHappenException::class, $expectedMessage);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/EnforceControllerSuffix');
    }

    /** @return Iterator<array{string, string}> */
    public static function provideViolations(): Iterator
    {
        yield ['missing_suffix.php.inc', 'Class "UserShow" is the controller for route "GET /user" and does not end in Controller. Rename it UserShowController.'];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/enforce_controller_suffix.php';
    }
}
