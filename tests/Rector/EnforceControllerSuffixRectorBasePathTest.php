<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use ZeroToProd\LaravelRector\Tests\Rector\Concerns\BindsRoutes;

/**
 * With nothing booted, the rule boots the application at the configured base path and holds
 * the fixture to the routes that application registers.
 */
final class EnforceControllerSuffixRectorBasePathTest extends RectorTestCase
{
    use BindsRoutes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unbindRoutes();
    }

    protected function tearDown(): void
    {
        $this->unbindRoutes();

        // Booting the application installed the framework's handlers over the runner's
        restore_error_handler();
        restore_exception_handler();

        parent::tearDown();
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/EnforceControllerSuffixBasePath');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/enforce_controller_suffix_base_path.php';
    }
}
