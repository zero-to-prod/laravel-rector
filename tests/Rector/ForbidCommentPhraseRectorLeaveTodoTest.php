<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

final class ForbidCommentPhraseRectorLeaveTodoTest extends RectorTestCase
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
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/ForbidCommentPhraseLeaveTodo');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/forbid_comment_phrase_leave_todo.php';
    }
}
