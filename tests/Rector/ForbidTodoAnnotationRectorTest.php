<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Rector\Exception\ShouldNotHappenException;

final class ForbidTodoAnnotationRectorTest extends RectorTestCase
{
    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideViolations')]
    public function test_reports_a_comment_carrying_the_annotation(string $fixture, string $expectedMessage): void
    {
        expect(fn () => $this->doTestFile(__DIR__.'/Violation/ForbidTodoAnnotation/'.$fixture))
            ->toThrow(ShouldNotHappenException::class, $expectedMessage);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/ForbidTodoAnnotation');
    }

    /** @return Iterator<array{string, string}> */
    public static function provideViolations(): Iterator
    {
        yield ['line_comment.php.inc', 'Comment carries a TODO annotation: "@TODO handle the empty case"'];
        yield ['doc_comment.php.inc', 'Comment carries a TODO annotation: "@todo drop the cache"'];
        yield ['hash_comment_outside_a_namespace.php.inc', 'Comment carries a TODO annotation: "@todo drop this file"'];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/forbid_todo_annotation.php';
    }
}
