<?php

declare(strict_types=1);

namespace ContentBuilder\Tests\Unit\Plugin;

use CB\Component\Contentbuilderng\Site\Service\StatsService;
use CB\Plugin\Content\ContentbuilderngStats\Service\DisplayOptionsService;
use CB\Plugin\Content\ContentbuilderngStats\Service\IdSumService;
use CB\Plugin\Content\ContentbuilderngStats\Service\PiePresentationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CbStatsDisplayOptionsServiceTest extends TestCase
{
    public function testMissingLimitPreservesEveryItem(): void
    {
        $items = $this->items();

        self::assertNull(DisplayOptionsService::parseLimit([]));
        self::assertSame($items, DisplayOptionsService::applyLimit($items, null));
    }

    #[DataProvider('validLimitProvider')]
    public function testPositiveLimitsAreAccepted(string $raw, int $expected): void
    {
        self::assertSame($expected, DisplayOptionsService::parseLimit(['limit' => $raw]));
    }

    public static function validLimitProvider(): array
    {
        return [['1', 1], ['10', 10], ['50', 50]];
    }

    #[DataProvider('invalidLimitProvider')]
    public function testInvalidLimitsAreRejected(string $raw): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(DisplayOptionsService::INVALID_LIMIT);

        DisplayOptionsService::parseLimit(['limit' => $raw]);
    }

    public static function invalidLimitProvider(): array
    {
        return [[''], ['abc'], ['0'], ['-1'], ['1.5'], ['999999999999999999999999999999']];
    }

    public function testLimitIsAppliedAfterTitleAscendingSort(): void
    {
        $sorted = StatsService::normalizeFieldStats(
            ['Zulu' => 1, 'Alpha' => 4, 'Bravo' => 2],
            'title',
            'asc',
            'en-GB'
        );

        self::assertSame(['Alpha', 'Bravo'], array_column(DisplayOptionsService::applyLimit($sorted, 2), 'label'));
    }

    public function testLimitIsAppliedAfterTitleDescendingSort(): void
    {
        $sorted = StatsService::normalizeFieldStats(
            ['Alpha' => 4, 'Zulu' => 1, 'Bravo' => 2],
            'title',
            'desc',
            'en-GB'
        );

        self::assertSame(['Zulu', 'Bravo'], array_column(DisplayOptionsService::applyLimit($sorted, 2), 'label'));
    }

    #[DataProvider('valueSortProvider')]
    public function testLimitIsAppliedAfterValueSort(string $direction, array $expected): void
    {
        $sorted = StatsService::normalizeFieldStats(
            ['Alpha' => 4, 'Bravo' => 2, 'Charlie' => 8],
            'value',
            $direction,
            'en-GB'
        );

        self::assertSame($expected, array_column(DisplayOptionsService::applyLimit($sorted, 2), 'label'));
    }

    public static function valueSortProvider(): array
    {
        return [
            ['asc', ['Bravo', 'Alpha']],
            ['desc', ['Charlie', 'Alpha']],
        ];
    }

    public function testLimitRecalculatesTheVisibleTotal(): void
    {
        $items = [
            ['label' => 'Visible', 'value' => 40],
            ['label' => 'Hidden', 'value' => 60],
        ];
        $limited = DisplayOptionsService::applyLimit($items, 1);

        self::assertSame(100, array_sum(array_column($items, 'value')));
        self::assertSame(40, array_sum(array_column($limited, 'value')));
    }

    public function testPiePercentagesUseTheLimitedTotal(): void
    {
        $limited = DisplayOptionsService::applyLimit([
            ['label' => 'First', 'value' => 30],
            ['label' => 'Second', 'value' => 10],
            ['label' => 'Hidden', 'value' => 60],
        ], 2);
        $presentation = PiePresentationService::prepare($limited, 'en-GB');

        self::assertSame(40, $presentation['total']);
        self::assertSame([75.0, 25.0], array_column($presentation['items'], 'percentage'));
        self::assertSame(['First', 'Second'], array_column($presentation['items'], 'label'));
    }

    public function testIdSumIsMergedBeforeLimitAndVisibleTotalCalculation(): void
    {
        $merged = IdSumService::mergePayloads([
            ['records' => ['total' => 50], 'field' => ['values' => ['Club A' => 30, 'Club B' => 20]]],
            ['records' => ['total' => 50], 'field' => ['values' => ['Club A' => 10, 'Club C' => 40]]],
        ]);
        $sorted = StatsService::normalizeFieldStats($merged['field']['values'], 'value', 'desc', 'en-GB');
        $limited = DisplayOptionsService::applyLimit($sorted, 1);

        self::assertSame([['label' => 'Club A', 'value' => 40]], $limited);
        self::assertSame(40, array_sum(array_column($limited, 'value')));
    }

    public function testTotalIsDisplayedByDefaultAndHiddenOnlyByExactOption(): void
    {
        self::assertFalse(DisplayOptionsService::hidesTotal([]));
        self::assertTrue(DisplayOptionsService::hidesTotal(['total' => 'hide']));
        self::assertTrue(DisplayOptionsService::hidesTotal(['total' => ' HIDE ']));
    }

    public function testInvalidTotalOptionIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(DisplayOptionsService::INVALID_TOTAL);

        DisplayOptionsService::hidesTotal(['total' => 'no']);
    }

    public function testPluginAppliesLimitOnceAfterNormalizedStatisticsAndHidesRenderedTotals(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 4) . '/plugins/content/contentbuilderng_cbstats/src/Extension/ContentbuilderngStats.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('DisplayOptionsService::applyLimit($fullFieldStats, $limit)', $source);
        self::assertStringContainsString("\$fieldTotal = array_sum(array_column(\$fieldStats, 'value'));", $source);
        self::assertStringContainsString("if (!\$hideTotal) {\n            \$html .= '<tfoot>", $source);
        self::assertStringContainsString("if (!\$hideTotal) {\n            \$html .= '<div class=\"cbstats-total-box\">", $source);
        self::assertStringContainsString("'total' => (string) StatsService::resolveCbstatsOutput", $source);
    }

    /** @return list<array{label: string, value: int}> */
    private function items(): array
    {
        return [
            ['label' => 'Alpha', 'value' => 4],
            ['label' => 'Bravo', 'value' => 2],
            ['label' => 'Charlie', 'value' => 9],
        ];
    }
}
