<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Rector\Exception\ShouldNotHappenException;

final class ForbidCommentPhraseRectorTest extends RectorTestCase
{
    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    #[WithoutErrorHandler]
    #[DataProvider('provideViolations')]
    public function test_reports_a_comment_carrying_a_configured_phrase(string $fixture, string $expectedMessage): void
    {
        expect(fn () => $this->doTestFile(__DIR__.'/Violation/ForbidCommentPhrase/'.$fixture))
            ->toThrow(ShouldNotHappenException::class, $expectedMessage);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/ForbidCommentPhrase');
    }

    /** @return Iterator<array{string, string}> */
    public static function provideViolations(): Iterator
    {
        yield ['line_comment.php.inc', 'Comment carries the forbidden phrase hack: "A HACK until the API settles"'];
        yield ['doc_comment.php.inc', 'Comment carries the forbidden phrase /fixme(\s|$)/i: "FIXME once the cache is gone"'];
        yield ['hash_comment_outside_a_namespace.php.inc', 'Comment carries the forbidden phrase hack: "a hack of a file"'];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/forbid_comment_phrase.php';
    }
}
