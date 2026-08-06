<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Site;

use CB\Component\Contentbuilderng\Site\Service\CompactPaginationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CompactPaginationServiceTest extends TestCase
{
    /**
     * @return iterable<string,array{int,int,array<int,int|null>}>
     */
    public static function pageCases(): iterable
    {
        yield 'no page' => [0, 1, []];
        yield 'one page' => [1, 1, [1]];
        yield 'two pages' => [2, 1, [1, 2]];
        yield 'five pages' => [5, 3, [1, 2, 3, 4, 5]];
        yield 'six pages' => [6, 1, [1, 2, 3, 4, 5, 6]];
        yield 'first of twenty' => [20, 1, [1, 2, 3, 4, 5, null, 19, 20]];
        yield 'middle of twenty' => [20, 10, [1, 2, null, 8, 9, 10, 11, 12, null, 19, 20]];
        yield 'last of twenty' => [20, 20, [1, 2, null, 16, 17, 18, 19, 20]];
        yield 'first of fifty' => [50, 1, [1, 2, 3, 4, 5, null, 49, 50]];
        yield 'middle of fifty' => [50, 10, [1, 2, null, 8, 9, 10, 11, 12, null, 49, 50]];
        yield 'near end of fifty' => [50, 49, [1, 2, null, 46, 47, 48, 49, 50]];
    }

    #[DataProvider('pageCases')]
    public function testVisiblePages(int $total, int $current, array $expected): void
    {
        self::assertSame($expected, CompactPaginationService::pages($total, $current));
    }

    public function testCurrentPageIsClamped(): void
    {
        self::assertSame([1, 2, 3, 4, 5, null, 19, 20], CompactPaginationService::pages(20, -5));
        self::assertSame([1, 2, null, 16, 17, 18, 19, 20], CompactPaginationService::pages(20, 99));
    }

    public function testLocalWindowSupportsResponsiveRendering(): void
    {
        self::assertTrue(CompactPaginationService::isInLocalWindow(10, 50, 10));
        self::assertTrue(CompactPaginationService::isInLocalWindow(12, 50, 10));
        self::assertFalse(CompactPaginationService::isInLocalWindow(2, 50, 10));
        self::assertTrue(CompactPaginationService::isInLocalWindow(1, 50, 1));
        self::assertTrue(CompactPaginationService::isInLocalWindow(5, 50, 1));
    }
}
