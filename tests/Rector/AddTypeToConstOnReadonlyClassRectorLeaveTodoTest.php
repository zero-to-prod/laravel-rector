<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

final class AddTypeToConstOnReadonlyClassRectorLeaveTodoTest extends RectorTestCase
{
    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/AddTypeToConstOnReadonlyClassLeaveTodo');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/add_type_to_const_on_readonly_class_leave_todo.php';
    }
}
