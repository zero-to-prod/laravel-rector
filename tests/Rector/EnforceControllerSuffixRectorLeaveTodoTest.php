<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use ZeroToProd\LaravelRector\Tests\Rector\Concerns\BindsRoutes;

final class EnforceControllerSuffixRectorLeaveTodoTest extends RectorTestCase
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

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/EnforceControllerSuffixLeaveTodo');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/enforce_controller_suffix_leave_todo.php';
    }
}
