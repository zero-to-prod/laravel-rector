<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

final class AddTypeToConstOnReadonlyClassRectorTest extends RectorTestCase
{
    #[WithoutErrorHandler]
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * Looking a constant up on the parents needs the class under test to be reflectable,
     * which it only is once the directory it lives in is indexed as a source.
     */
    #[WithoutErrorHandler]
    public function test_skips_a_constant_a_parent_declares(): void
    {
        $this->doTestFile(__DIR__.'/ReflectedFixture/AddTypeToConstOnReadonlyClass/skip_constant_declared_by_parent.php.inc', true);
    }

    /** @return Iterator<array{string}> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/AddTypeToConstOnReadonlyClass');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/add_type_to_const_on_readonly_class.php';
    }
}
