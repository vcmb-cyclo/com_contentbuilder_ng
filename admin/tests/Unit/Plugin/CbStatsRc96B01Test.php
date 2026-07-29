<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Plugin;

use CB\Component\Contentbuilderng\Site\Service\StatsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CbStatsRc96B01Test extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testRangesPreserveDeclarationOrderAndInclusiveBounds(): void
    {
        $ranges = StatsService::parseFieldStatsRanges('18-29;30-39;60+');

        self::assertSame(['18-29', '30-39', '60+'], array_column($ranges, 'label'));
        self::assertSame([
            '18-29' => 2,
            '30-39' => 2,
            '60+' => 2,
        ], StatsService::applyFieldStatsRanges([
            '' => 4,
            '17' => 1,
            '18' => 1,
            '29' => 1,
            '30' => 1,
            '39' => 1,
            '59' => 1,
            '60' => 1,
            '75' => 1,
            'invalid' => 8,
        ], $ranges));
    }

    public function testOverlappingRangesCountEveryMembershipIndependently(): void
    {
        $ranges = StatsService::parseFieldStatsRanges('18-35;30-45;40-55;50+');
        $counts = StatsService::applyFieldStatsRanges([
            '18' => 1,
            '30' => 2,
            '40' => 3,
            '50' => 4,
            '60' => 5,
        ], $ranges);

        self::assertSame([
            '18-35' => 3,
            '30-45' => 5,
            '40-55' => 7,
            '50+' => 9,
        ], $counts);
        self::assertGreaterThan(array_sum([1, 2, 3, 4, 5]), array_sum($counts));
    }

    #[DataProvider('invalidRangesProvider')]
    public function testInvalidRangeReportsTheExactSuspiciousItem(string $syntax, string $item): void
    {
        try {
            StatsService::parseFieldStatsRanges($syntax);
            self::fail('Invalid ranges syntax was accepted.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(StatsService::CBSTATS_ERROR_INVALID_RANGES, $exception->getCode());
            self::assertSame($item, $exception->getMessage());
        }
    }

    public static function invalidRangesProvider(): iterable
    {
        yield ['Gravel;Route', 'Gravel'];
        yield ['0-17;18/29;60+', '18/29'];
        yield ['18-', '18-'];
        yield ['+60', '+60'];
        yield ['18--29', '18--29'];
        yield ['20-10', '20-10'];
        yield ['0-17;;60+', ''];
    }

    public function testMixedFieldDataIgnoresTextWithoutTurningItIntoRangeSyntaxError(): void
    {
        $ranges = StatsService::parseFieldStatsRanges('18-29;30-49');

        self::assertSame([
            '18-29' => 2,
            '30-49' => 1,
        ], StatsService::applyFieldStatsRanges([
            '18' => 1,
            '27' => 1,
            'Gravel' => 1,
            '42' => 1,
        ], $ranges));
    }

    public function testTitlesApplyToRangesWithoutChangingTheirOrder(): void
    {
        $ranges = StatsService::parseFieldStatsRanges('18-29;30-39;60+');
        $counts = StatsService::applyFieldStatsRanges(['18' => 2, '35' => 3, '70' => 4], $ranges);

        self::assertSame([
            ['label' => '18 to 29', 'value' => 2],
            ['label' => '30 to 39', 'value' => 3],
            ['label' => '60 and over', 'value' => 4],
        ], StatsService::normalizeFieldStats(
            $counts,
            'none',
            'asc',
            'en-GB',
            [],
            ['18-29' => '18 to 29', '30-39' => '30 to 39', '60+' => '60 and over']
        ));
    }

    public function testAverageUsesOriginalNumericValuesAndIgnoresInvalidValues(): void
    {
        $aggregates = StatsService::computeFieldAggregates([
            '20' => 1,
            '30' => 1,
            '40' => 1,
            '50' => 1,
            '' => 5,
            'n/a' => 9,
        ]);

        self::assertSame(35.0, $aggregates['avg']);
    }

    public function testNewOutputsUseSharedPayloadAndUrlPipeline(): void
    {
        $plugin = (string) file_get_contents(
            self::ROOT . '/plugins/content/contentbuilderng_cbstats/src/Extension/ContentbuilderngStats.php'
        );
        $controller = (string) file_get_contents(self::ROOT . '/site/src/Controller/ApiController.php');

        foreach (['avg', 'histogram', 'line', 'radar'] as $output) {
            self::assertStringContainsString("'$output'", $plugin);
            self::assertStringContainsString("'$output'", $controller);
        }

        self::assertStringContainsString('StatsService::parseFieldStatsRanges($ranges)', $plugin);
        self::assertStringContainsString('StatsService::parseFieldStatsRanges($ranges)', $controller);
        self::assertStringContainsString(
            "['type' => \$output, 'items' => \$chartItems, 'showValues' => !\$hideOptions['values']]",
            $plugin
        );
        self::assertStringContainsString("private const RADAR_MIN_AXES = 3", $plugin);
        self::assertStringContainsString("private const RADAR_MAX_AXES = 8", $plugin);
        self::assertStringContainsString('MAX_FRACTION_DIGITS, 2', $plugin);
        self::assertStringContainsString("htmlspecialchars(\$json", $plugin);
    }

    public function testInvalidRangesDiagnosticIsSharedByArticleOutputsAndUrlApi(): void
    {
        $plugin = (string) file_get_contents(
            self::ROOT . '/plugins/content/contentbuilderng_cbstats/src/Extension/ContentbuilderngStats.php'
        );
        $controller = (string) file_get_contents(self::ROOT . '/site/src/Controller/ApiController.php');

        foreach (['table', 'json', 'pie', 'bar', 'histogram', 'line', 'radar'] as $output) {
            self::assertStringContainsString("'$output'", $plugin);
            self::assertStringContainsString("'$output'", $controller);
        }

        self::assertStringContainsString(
            '$exception->getCode() === StatsService::CBSTATS_ERROR_INVALID_RANGES',
            $plugin
        );
        self::assertStringContainsString(
            'htmlspecialchars($message, ENT_QUOTES, \'UTF-8\')',
            $plugin
        );
        self::assertStringContainsString(
            "'COM_CONTENTBUILDERNG_API_CBSTATS_INVALID_RANGES'",
            $controller
        );
        self::assertStringContainsString(
            'StatsService::parseFieldStatsRanges($ranges)',
            $controller
        );
    }

    public function testChartAssetsAreLocalResponsiveAndInitialisedOnce(): void
    {
        $javascript = (string) file_get_contents(
            self::ROOT . '/plugins/content/contentbuilderng_cbstats/media/js/cbstats-charts.js'
        );
        $css = (string) file_get_contents(
            self::ROOT . '/plugins/content/contentbuilderng_cbstats/media/css/cbstats-charts.css'
        );
        $assets = json_decode((string) file_get_contents(
            self::ROOT . '/plugins/content/contentbuilderng_cbstats/media/joomla.asset.json'
        ), true, 512, JSON_THROW_ON_ERROR);

        self::assertStringContainsString("root.dataset.cbstatsInitialised === 'true'", $javascript);
        self::assertStringContainsString("['histogram', 'line', 'radar'].includes(output)", $javascript);
        self::assertStringContainsString("querySelectorAll('[data-cbstats-chart]').forEach(initialise)", $javascript);
        self::assertStringContainsString('overflow-x: auto', $css);
        self::assertStringContainsString('calc(var(--cbstats-chart-items) * 4.5rem)', $css);
        self::assertContains(
            'plg_content_contentbuilderng_cbstats.chartjs',
            array_column($assets['assets'], 'name')
        );
    }

    public function testNewMessagesExistInAllMaintainedLanguages(): void
    {
        foreach (['en-GB', 'fr-FR', 'de-DE'] as $locale) {
            $pluginStrings = parse_ini_file(
                self::ROOT . '/plugins/content/contentbuilderng_cbstats/language/'
                . $locale . '/plg_content_contentbuilderng_cbstats.ini'
            );
            $siteStrings = parse_ini_file(
                self::ROOT . '/site/language/' . $locale . '/com_contentbuilderng.ini'
            );
            $adminStrings = parse_ini_file(
                self::ROOT . '/admin/language/' . $locale . '/com_contentbuilderng.ini'
            );

            self::assertIsArray($pluginStrings);
            self::assertIsArray($siteStrings);
            self::assertIsArray($adminStrings);
            self::assertArrayHasKey(
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_RANGES',
                $pluginStrings
            );
            self::assertSame(
                $pluginStrings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_RANGES'],
                $siteStrings['COM_CONTENTBUILDERNG_API_CBSTATS_INVALID_RANGES']
            );
            self::assertSame(
                $siteStrings['COM_CONTENTBUILDERNG_API_CBSTATS_INVALID_RANGES'],
                $adminStrings['COM_CONTENTBUILDERNG_API_CBSTATS_INVALID_RANGES']
            );
            self::assertStringContainsString('ranges', $pluginStrings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_RANGES']);
            self::assertStringContainsString('%s', $pluginStrings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_RANGES']);
            self::assertStringContainsString(
                'minimum-maximum',
                $pluginStrings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_RANGES']
            );
            self::assertArrayHasKey('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_CHART_ARIA_LABEL', $pluginStrings);
            self::assertArrayHasKey('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_RADAR_TOO_FEW', $pluginStrings);
            self::assertArrayHasKey('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_RADAR_TOO_MANY', $pluginStrings);
        }
    }
}
