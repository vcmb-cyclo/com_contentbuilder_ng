<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Helper;

use CB\Component\Contentbuilderng\Administrator\Helper\ListLimitHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 3) . '/src/Helper/ListLimitHelper.php';

final class ListLimitHelperTest extends TestCase
{
    /** @return array<string, array{0:mixed, 1:int}> */
    public static function storedValueProvider(): array
    {
        return [
            'empty inherits' => ['', -1],
            'legacy negative inherits' => [-4, -1],
            'all remains zero' => [0, 0],
            'custom remains unchanged' => [6, 6],
            'standard remains unchanged' => [20, 20],
        ];
    }

    #[DataProvider('storedValueProvider')]
    public function testNormalizesStoredViewValue(mixed $value, int $expected): void
    {
        self::assertSame($expected, ListLimitHelper::normalizeStoredViewValue($value));
    }

    public function testViewInheritanceIsDynamicAndAllIsExplicit(): void
    {
        self::assertSame(20, ListLimitHelper::resolveViewValue(-1, 20));
        self::assertSame(25, ListLimitHelper::resolveViewValue(-1, 25));
        self::assertSame(0, ListLimitHelper::resolveViewValue(0, 25));
        self::assertSame(6, ListLimitHelper::resolveViewValue(6, 25));
    }

    public function testMenuInheritanceKeepsViewAllAndCustomValues(): void
    {
        self::assertSame(20, ListLimitHelper::resolveMenuValue('', 20));
        self::assertSame(20, ListLimitHelper::resolveMenuValue(-1, 20));
        self::assertSame(0, ListLimitHelper::resolveMenuValue('', 0));
        self::assertSame(0, ListLimitHelper::resolveMenuValue(0, 20));
        self::assertSame(6, ListLimitHelper::resolveMenuValue(6, 20));
    }

    public function testRuntimeValueUsesFallbackOnlyForInvalidNegativeValues(): void
    {
        self::assertSame(0, ListLimitHelper::normalizeRuntimeValue(0, 20));
        self::assertSame(6, ListLimitHelper::normalizeRuntimeValue(6, 20));
        self::assertSame(20, ListLimitHelper::normalizeRuntimeValue(-1, 20));
    }

    public function testParsesNormalizesAndDeduplicatesPaginationChoicesInOrder(): void
    {
        self::assertSame(
            [20, 5, 0, 100],
            ListLimitHelper::parsePaginationChoices(' 20, 5, All, 20, 1 0 0 ')
        );
        self::assertSame(
            '20,5,All,100',
            ListLimitHelper::formatPaginationChoices([20, 5, 0, 100])
        );
    }

    #[DataProvider('customPaginationChoiceProvider')]
    public function testInsertsCustomPaginationChoiceAtItsNumericPosition(int $current, array $expected): void
    {
        self::assertSame(
            $expected,
            ListLimitHelper::insertCurrentPaginationChoice([5, 10, 20, 25, 50, 100], $current)
        );
    }

    /** @return array<string, array{0:int, 1:list<int>}> */
    public static function customPaginationChoiceProvider(): array
    {
        return [
            'before first choice' => [3, [3, 5, 10, 20, 25, 50, 100]],
            'between choices' => [18, [5, 10, 18, 20, 25, 50, 100]],
            'after last choice' => [200, [5, 10, 20, 25, 50, 100, 200]],
            'existing choice unchanged' => [20, [5, 10, 20, 25, 50, 100]],
        ];
    }

    #[DataProvider('invalidPaginationChoicesProvider')]
    public function testRejectsInvalidPaginationChoices(string $configured): void
    {
        $this->expectException(\UnexpectedValueException::class);
        ListLimitHelper::parsePaginationChoices($configured);
    }

    /** @return array<string, array{0:string}> */
    public static function invalidPaginationChoicesProvider(): array
    {
        return [
            'empty' => [' , '],
            'zero' => ['5,0,20'],
            'negative' => ['5,-10,20'],
            'word' => ['5,Everything,20'],
            'decimal' => ['5,10.5,20'],
        ];
    }
}
