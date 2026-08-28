<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Plugin;

use CB\Component\Contentbuilderng\Site\Service\StatsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CbStatsRc96B01Test extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testIntervalGroupsPreserveDeclarationOrderAndInclusiveBounds(): void
    {
        $groups = StatsService::parseFieldStatsGroups('18-29;30-39;60+');

        self::assertSame(['18-29', '30-39', '60+'], array_column($groups, 'label'));
        self::assertSame([
            '18-29' => 2,
            '30-39' => 2,
            '60+' => 2,
            '17' => 1,
            '59' => 1,
            'invalid' => 8,
        ], StatsService::applyFieldStatsGroups([
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
        ], $groups));
    }

    public function testUpperBoundOnlyIntervalGroupIsInclusive(): void
    {
        $groups = StatsService::parseFieldStatsGroups('13-;14-17;70+');
        self::assertSame([
            '13-' => 2,
            '14-17' => 1,
            '70+' => 1,
            '69' => 1,
        ], StatsService::applyFieldStatsGroups([
            '12' => 1,
            '13' => 1,
            '14' => 1,
            '69' => 1,
            '70' => 1,
        ], $groups));
    }
    public function testOverlappingGroupsCountEveryMembershipIndependently(): void
    {
        $groups = StatsService::parseFieldStatsGroups('18-35;30-45;40-55;50+');
        $counts = StatsService::applyFieldStatsGroups([
            '18' => 1,
            '30' => 2,
            '40' => 3,
            '50' => 4,
            '60' => 5,
        ], $groups);

        self::assertSame([
            '18-35' => 3,
            '30-45' => 5,
            '40-55' => 7,
            '50+' => 9,
        ], $counts);
        self::assertGreaterThan(array_sum([1, 2, 3, 4, 5]), array_sum($counts));
    }

    #[DataProvider('invalidGroupsProvider')]
    public function testInvalidGroupReportsTheExactSuspiciousItem(string $syntax, string $item): void
    {
        try {
            StatsService::parseFieldStatsGroups($syntax);
            self::fail('Invalid groups syntax was accepted.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(StatsService::CBSTATS_ERROR_INVALID_GROUPS, $exception->getCode());
            self::assertSame($item, $exception->getMessage());
        }
    }

    public static function invalidGroupsProvider(): iterable
    {
        yield ['Gravel;Route', 'Gravel'];
        yield ['0-17;18/29;60+', '18/29'];
        yield ['+60', '+60'];
        yield ['18--29', '18--29'];
        yield ['20-10', '20-10'];
        yield ['0-17;;60+', ''];
    }

    public function testExplicitValueGroupsSupportNonContiguousNumericAndTextValues(): void
    {
        $groups = StatsService::parseFieldStatsGroups(
            '1,2,7,9=Group 1;3,4,8=Group 2;Gravel,Route=Surface'
        );

        self::assertSame([
            '1,2,7,9' => 10,
            '3,4,8' => 6,
            'Gravel,Route' => 11,
            'Other' => 20,
        ], StatsService::applyFieldStatsGroups([
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 1,
            '7' => 3,
            '8' => 2,
            '9' => 4,
            'Gravel' => 5,
            'Route' => 6,
            'Other' => 20,
        ], $groups));
        self::assertSame(['Group 1', 'Group 2', 'Surface'], array_column($groups, 'title'));
    }

    public function testMixedFieldDataPreservesTextThatDoesNotMatchANumericGroup(): void
    {
        $groups = StatsService::parseFieldStatsGroups('18-29;30-49');

        self::assertSame([
            '18-29' => 2,
            '30-49' => 1,
            'Gravel' => 1,
        ], StatsService::applyFieldStatsGroups([
            '18' => 1,
            '27' => 1,
            'Gravel' => 1,
            '42' => 1,
        ], $groups));
    }

    public function testTitlesApplyToGroupsWithoutChangingTheirOrder(): void
    {
        $groups = StatsService::parseFieldStatsGroups('18-29;30-39;60+');
        $counts = StatsService::applyFieldStatsGroups(['18' => 2, '35' => 3, '70' => 4], $groups);

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

        foreach (['percentage', 'progress'] as $output) {
            self::assertStringContainsString("'$output'", $plugin);
            self::assertStringContainsString("'$output'", $controller);
        }
        self::assertStringContainsString("getString('groupset', '')", $controller);
        self::assertStringContainsString('StatsService::parseFieldStatsGroups(', $plugin);
        self::assertStringContainsString('StatsService::parseFieldStatsGroups(', $controller);
        self::assertStringContainsString(
            "['type' => \$output, 'items' => \$items]",
            $plugin
        );
        self::assertStringContainsString("private const RADAR_MIN_AXES = 3", $plugin);
        self::assertStringContainsString("private const RADAR_MAX_AXES = 8", $plugin);
        self::assertStringContainsString('MAX_FRACTION_DIGITS, 2', $plugin);
        self::assertStringContainsString("htmlspecialchars(\$json", $plugin);
    }

    public function testTitleSetImportIsDisabledWhileRowsAreSelected(): void
    {
        $template = (string) file_get_contents(self::ROOT . '/admin/tmpl/titlesets/default.php');
        $view = (string) file_get_contents(self::ROOT . '/admin/src/View/Titlesets/HtmlView.php');

        self::assertStringContainsString("function syncImportState()", $template);
        self::assertStringContainsString("document.querySelector('[data-cb-titlesets-import-button]')", $template);
        self::assertStringContainsString("importToolbarButton.disabled = disabled", $template);
        self::assertStringContainsString("'data-cb-titlesets-import-button' => ''", $view);
        self::assertStringContainsString("input[name=\"cid[]\"]", $template);
        self::assertStringContainsString("if (task === 'titlesets.import')", $template);
        self::assertStringContainsString("return false;", $template);
    }

    public function testHelpAndApiUseTheCanonicalOutputOrder(): void
    {
        $order = [
            'total', 'table', 'pie', 'bar', 'histogram', 'line', 'radar', 'json',
            'sum', 'avg', 'remaining', 'percentage', 'progress', 'distinct', 'view_name',
        ];

        foreach (['en-GB', 'fr-FR', 'de-DE'] as $locale) {
            $strings = parse_ini_file(
                self::ROOT . '/plugins/content/contentbuilderng_cbstats/language/' . $locale
                . '/plg_content_contentbuilderng_cbstats.ini'
            );
            self::assertIsArray($strings);
            $help = (string) $strings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_TEXT'];
            $position = -1;

            foreach ($order as $output) {
                $next = strpos($help, '<code>' . $output . '</code>', $position + 1);
                self::assertNotFalse($next, $locale . ': missing ' . $output);
                self::assertGreaterThan($position, $next, $locale . ': misplaced ' . $output);
                $position = $next;
            }
        }

        $canonical = 'total, table, pie, bar, histogram, line, radar, json, sum, min, max, avg, '
            . 'remaining, percentage, progress, distinct, view_name';
        $apiHelp = (string) file_get_contents(self::ROOT . '/admin/layouts/form/api_tab.php');
        $openApi = (string) file_get_contents(self::ROOT . '/admin/src/Service/OpenApiSpecService.php');

        self::assertStringContainsString($canonical, $apiHelp);
        self::assertStringContainsString(
            "'total', 'table', 'pie', 'bar', 'histogram', 'line', 'radar', 'json',",
            $openApi
        );
        self::assertStringContainsString(
            "'sum', 'min', 'max', 'avg', 'remaining', 'percentage', 'progress', 'distinct', 'view_name',",
            $openApi
        );
    }

    public function testInvalidGroupsDiagnosticIsSharedByArticleOutputsAndUrlApi(): void
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
            '$exception->getCode() === StatsService::CBSTATS_ERROR_INVALID_GROUPS',
            $plugin
        );
        self::assertStringContainsString(
            'htmlspecialchars($message, ENT_QUOTES, \'UTF-8\')',
            $plugin
        );
        self::assertStringContainsString(
            "'COM_CONTENTBUILDERNG_API_CBSTATS_INVALID_GROUPS'",
            $controller
        );
        self::assertStringContainsString(
            'StatsService::parseFieldStatsGroups(',
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
        self::assertStringNotContainsString('overflow-x: auto', $css);
        self::assertStringContainsString('max-width: 100%', $css);
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
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_GROUPS',
                $pluginStrings
            );
            self::assertSame(
                $pluginStrings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_GROUPS'],
                $siteStrings['COM_CONTENTBUILDERNG_API_CBSTATS_INVALID_GROUPS']
            );
            self::assertSame(
                $siteStrings['COM_CONTENTBUILDERNG_API_CBSTATS_INVALID_GROUPS'],
                $adminStrings['COM_CONTENTBUILDERNG_API_CBSTATS_INVALID_GROUPS']
            );
            self::assertStringContainsString('groups', $pluginStrings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_GROUPS']);
            self::assertStringContainsString('%s', $pluginStrings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_GROUPS']);
            self::assertStringContainsString(
                'minimum-maximum',
                $pluginStrings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_INVALID_GROUPS']
            );
            self::assertArrayHasKey('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_CHART_ARIA_LABEL', $pluginStrings);
            self::assertArrayHasKey('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_RADAR_TOO_FEW', $pluginStrings);
            self::assertArrayHasKey('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_RADAR_TOO_MANY', $pluginStrings);
        }
    }
}
