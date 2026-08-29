<?php

declare(strict_types=1);

namespace ContentBuilder\Tests\Unit\Plugin;

use CB\Component\Contentbuilderng\Site\Service\StatsService;
use CB\Component\Contentbuilderng\Site\Service\StatsHideOptionsService;
use CB\Plugin\Content\ContentbuilderngStats\Service\DisplayOptionsService;
use CB\Plugin\Content\ContentbuilderngStats\Service\IdSumService;
use CB\Plugin\Content\ContentbuilderngStats\Service\PiePresentationService;
use CB\Plugin\Content\ContentbuilderngStats\Service\TagSyntaxService;
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

    public function testMissingHidePreservesEveryDisplayElement(): void
    {
        self::assertSame(
            ['title' => false, 'total' => false, 'values' => false, 'graph' => false],
            StatsHideOptionsService::fromAttributes([])
        );
    }

    #[DataProvider('validHideProvider')]
    public function testHideValuesAreNormalized(string $raw, array $expected): void
    {
        self::assertSame($expected, StatsHideOptionsService::parse($raw));
    }

    public static function validHideProvider(): iterable
    {
        yield ['title', ['title' => true, 'total' => false, 'values' => false, 'graph' => false]];
        yield ['total', ['title' => false, 'total' => true, 'values' => false, 'graph' => false]];
        yield ['values', ['title' => false, 'total' => false, 'values' => true, 'graph' => false]];
        yield ['graph', ['title' => false, 'total' => false, 'values' => false, 'graph' => true]];
        yield ['title|values|total', ['title' => true, 'total' => true, 'values' => true, 'graph' => false]];
        yield ['graph|title|total', ['title' => true, 'total' => true, 'values' => false, 'graph' => true]];
        yield ['title|total|values|graph', ['title' => true, 'total' => true, 'values' => true, 'graph' => true]];
    }

    public function testQuotedShortcodePreservesTheCompletePipeSeparatedHideValue(): void
    {
        $tag = '{CBStats id=25 field=age output=histogram hide="graph|total"}';
        self::assertSame(1, preg_match(TagSyntaxService::TAG_PATTERN, $tag, $matches));

        $attributes = TagSyntaxService::parseAttributes((string) ($matches[1] ?? ''));
        self::assertSame('graph|total', $attributes['hide']);
        self::assertSame(
            ['title' => false, 'total' => true, 'values' => false, 'graph' => true],
            StatsHideOptionsService::fromAttributes($attributes)
        );
    }

    public function testEncodedUrlPipeUsesTheSameNormalizedParserResult(): void
    {
        self::assertSame(
            StatsHideOptionsService::parse('graph|total'),
            StatsHideOptionsService::parse('graph%7Ctotal')
        );
    }

    #[DataProvider('invalidHideProvider')]
    public function testInvalidHideSyntaxIsRejected(string $raw, int $code, string $item): void
    {
        try {
            StatsHideOptionsService::parse($raw);
            self::fail('Invalid hide syntax was accepted.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame($code, $exception->getCode());
            self::assertSame($item, $exception->getMessage());
        }
    }

    public static function invalidHideProvider(): iterable
    {
        yield ['', StatsHideOptionsService::INVALID_ITEM, ''];
        yield ['table', StatsHideOptionsService::INVALID_ITEM, 'table'];
        yield ['total||values', StatsHideOptionsService::INVALID_ITEM, ''];
        yield ['total,values', StatsHideOptionsService::INVALID_SEPARATOR, 'total,values'];
        yield ['total;values', StatsHideOptionsService::INVALID_SEPARATOR, 'total;values'];
    }

    public function testLegacyTotalOptionIsRejectedWithoutAliasConversion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(StatsHideOptionsService::LEGACY_TOTAL);

        StatsHideOptionsService::fromAttributes(['total' => 'hide']);
    }

    public function testHideApplicabilityIsValidatedCentrally(): void
    {
        $totalOnly = StatsHideOptionsService::parse('total');
        StatsHideOptionsService::validateForOutput($totalOnly, 'table');
        StatsHideOptionsService::validateForOutput($totalOnly, 'pie');

        try {
            StatsHideOptionsService::validateForOutput(StatsHideOptionsService::parse('graph'), 'json');
            self::fail('hide=graph was accepted for JSON.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(StatsHideOptionsService::NOT_APPLICABLE, $exception->getCode());
            self::assertSame('graph|json', $exception->getMessage());
        }

        try {
            StatsHideOptionsService::validateForOutput(
                StatsHideOptionsService::parse('total|values|graph'),
                'radar'
            );
            self::fail('A fully hidden radar was accepted.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(StatsHideOptionsService::ALL_HIDDEN, $exception->getCode());
        }

        try {
            StatsHideOptionsService::validateForOutput(StatsHideOptionsService::parse('values'), 'avg');
            self::fail('hide=values was accepted for AVG.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame('values|avg', $exception->getMessage());
        }
    }

    #[DataProvider('nonApplicableHideProvider')]
    public function testNonGraphOutputsRejectIncompatibleHideOptions(
        string $output,
        string $hide,
        string $expectedMessage
    ): void {
        try {
            StatsHideOptionsService::validateForOutput(StatsHideOptionsService::parse($hide), $output);
            self::fail(sprintf('hide=%s was accepted for %s.', $hide, $output));
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(StatsHideOptionsService::NOT_APPLICABLE, $exception->getCode());
            self::assertSame($expectedMessage, $exception->getMessage());
        }
    }

    public static function nonApplicableHideProvider(): iterable
    {
        yield ['table', 'graph', 'graph|table'];
        yield ['json', 'values', 'values|json'];
        yield ['json', 'title', 'title|json'];
        yield ['min', 'graph', 'graph|min'];
        yield ['max', 'values', 'values|max'];
        yield ['avg', 'graph', 'graph|avg'];
    }

    #[DataProvider('chartOutputProvider')]
    public function testEveryChartOutputAcceptsIndividualAndCombinedHideOptions(string $output): void
    {
        foreach (['title', 'total', 'values', 'graph', 'title|total', 'title|values|graph', 'total|graph', 'values|graph'] as $hide) {
            StatsHideOptionsService::validateForOutput(StatsHideOptionsService::parse($hide), $output);
            self::addToAssertionCount(1);
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(StatsHideOptionsService::ALL_HIDDEN);
        StatsHideOptionsService::validateForOutput(
            StatsHideOptionsService::parse('total|values|graph'),
            $output
        );
    }

    public static function chartOutputProvider(): iterable
    {
        foreach (['pie', 'bar', 'histogram', 'line', 'radar'] as $output) {
            yield [$output];
        }
    }

    public function testHideSerializationUsesCanonicalOrder(): void
    {
        self::assertSame(
            'title|total|values',
            StatsHideOptionsService::serialize(StatsHideOptionsService::parse('values|title|total|values'))
        );
    }

    public function testTitleCanBeHiddenOnEveryHtmlOutput(): void
    {
        foreach (['total', 'table', 'pie', 'bar', 'histogram', 'line', 'radar', 'sum', 'min', 'max', 'avg', 'remaining', 'percentage', 'progress', 'distinct', 'view_name'] as $output) {
            StatsHideOptionsService::validateForOutput(StatsHideOptionsService::parse('title'), $output);
            self::addToAssertionCount(1);
        }
    }

    public function testPluginUsesNormalizedHideFlagsAcrossEveryChartRenderer(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 4) . '/plugins/content/contentbuilderng_cbstats/src/Extension/ContentbuilderngStats.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('DisplayOptionsService::applyLimit($fullFieldStats, $limit)', $source);
        self::assertStringContainsString("\$fieldTotal = array_sum(array_column(\$fieldStats, 'value'));", $source);
        self::assertStringContainsString('$displayTotal = $fieldTotal;', $source);
        self::assertStringNotContainsString('$displayTotal = $usesGroups ?', $source);
        self::assertMatchesRegularExpression("/if \(!\\\$hideOptions\['total'\]\) \{\\R\\s+\\\$html \.= '<tfoot>/", $source);
        self::assertMatchesRegularExpression("/if \(!\\\$hideOptions\['total'\]\) \{\\R\\s+\\\$html \.= '<div class=\"cbstats-total-box\">/", $source);
        self::assertStringContainsString("if (!\$hideOptions['graph']) {", $source);
        self::assertStringContainsString("if (!\$hideOptions['values']) {", $source);
        self::assertStringContainsString("['items' => \$items]", $source);
        self::assertStringContainsString("['type' => \$output, 'items' => \$items]", $source);
        self::assertStringNotContainsString("\$item['label'] = '';", $source);
        self::assertStringNotContainsString('prepareChartPayloadItems', $source);
        self::assertStringNotContainsString("'showValues' => !\$hideOptions['values']", $source);
        self::assertStringContainsString('StatsHideOptionsService::fromAttributes($attributes)', $source);
        self::assertStringContainsString("\$blockTitle = \$hideOptions['title'] ? ''", $source);
        self::assertStringNotContainsString('DisplayOptionsService::hidesTotal', $source);
        self::assertStringContainsString("'total' => (string) StatsService::resolveCbstatsOutput", $source);
    }

    public function testHideValuesDoesNotChangeTheGraphJavascript(): void
    {
        foreach (['cbstats-pie.js', 'cbstats-bar.js', 'cbstats-charts.js'] as $file) {
            $source = file_get_contents(
                dirname(__DIR__, 4) . '/plugins/content/contentbuilderng_cbstats/media/js/' . $file
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('showValues', $source);
        }

        $pie = (string) file_get_contents(
            dirname(__DIR__, 4) . '/plugins/content/contentbuilderng_cbstats/media/js/cbstats-pie.js'
        );
        $bar = (string) file_get_contents(
            dirname(__DIR__, 4) . '/plugins/content/contentbuilderng_cbstats/media/js/cbstats-bar.js'
        );
        $charts = (string) file_get_contents(
            dirname(__DIR__, 4) . '/plugins/content/contentbuilderng_cbstats/media/js/cbstats-charts.js'
        );
        self::assertStringContainsString('`${item.value} (${item.percentageLabel} %)`', $pie);
        self::assertStringContainsString('`${item.value} (${item.percentageLabel} %)`', $bar);
        self::assertStringContainsString('`${context.label}: ${context.formattedValue}`', $charts);
    }

    public function testArticleAndUrlPathsUseTheSameHideParserAndValidation(): void
    {
        $root = dirname(__DIR__, 4);
        $plugin = (string) file_get_contents(
            $root . '/plugins/content/contentbuilderng_cbstats/src/Extension/ContentbuilderngStats.php'
        );
        $api = (string) file_get_contents($root . '/site/src/Controller/ApiController.php');

        foreach ([$plugin, $api] as $source) {
            self::assertStringContainsString('StatsHideOptionsService::', $source);
            self::assertStringContainsString('StatsHideOptionsService::validateForOutput', $source);
        }
        self::assertStringContainsString('StatsHideOptionsService::fromAttributes($attributes)', $plugin);
        self::assertStringContainsString('StatsHideOptionsService::parse(', $api);
        self::assertStringContainsString("\$this->input->exists('total')", $api);
        self::assertStringNotContainsString("'total' => 'hide'", $api);
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
