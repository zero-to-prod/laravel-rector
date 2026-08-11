<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

final class RenameParamToMatchTypeExactCaseRectorLeaveTodoTest extends RectorTestCase
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
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/RenameParamToMatchTypeExactCaseLeaveTodo');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/rename_param_to_match_type_exact_case_leave_todo.php';
    }
}
