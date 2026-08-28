<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Plugin;

use CB\Plugin\Content\ContentbuilderngStats\Service\StatsTagValidationService;
use CB\Plugin\Content\ContentbuilderngStats\Service\TagSyntaxService;
use PHPUnit\Framework\TestCase;

final class CbStatsValidationServiceTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testIndependentSyntaxErrorsAreCollectedTogether(): void
    {
        $errors = StatsTagValidationService::validationErrors([
            'id' => '0',
            'output' => 'pie',
            'field' => '',
            'sort' => 'random',
            'dir' => 'sideways',
            'limit' => '0',
            'hide' => 'total|values|graph',
            'export' => 'csv',
            'background' => 'url(javascript:alert(1))',
            'add' => 'Route=five',
            'titles' => 'Route=',
            'headers' => 'Route',
            'groups' => 'young-old',
            'typo' => 'value',
        ]);

        self::assertSame(
            [
                'typo', 'id', 'field', 'sort', 'dir', 'limit', 'hide',
                'export', 'background', 'headers', 'add', 'titles', 'groups',
            ],
            array_column($errors, 'parameter')
        );
    }

    public function testValidViewAndManualSyntaxRemainAccepted(): void
    {
        self::assertSame([], StatsTagValidationService::validationErrors([
            'id' => '15',
            'field' => 'Distance',
            'output' => 'bar',
            'sort' => 'value',
            'dir' => 'desc',
            'limit' => '10',
            'hide' => 'values',
            'titleset' => 'departements-fr-FR.ini',
        ]));
        self::assertSame([], StatsTagValidationService::validationErrors([
            'source' => 'manual',
            'output' => 'pie',
            'values' => 'Short=12;Long=8',
            'sort' => 'label',
            'dir' => 'asc',
        ]));
    }

    public function testGroupsSyntaxIsAcceptedAndUnreleasedRangeNamesAreRejected(): void
    {
        self::assertSame([], StatsTagValidationService::validationErrors([
            'id' => '15',
            'field' => 'Category',
            'output' => 'bar',
            'groups' => '18-24;25-;70+;1,2,7,9=Group 1;3,4,8=Group 2',
            'groupset' => 'groups.ini',
        ]));

        $errors = StatsTagValidationService::validationErrors([
            'id' => '15',
            'field' => 'Category',
            'output' => 'bar',
            'ranges' => '18-24',
            'rangeset' => 'ages.ini',
        ]);

        self::assertSame(['ranges', 'rangeset'], array_column($errors, 'parameter'));
        self::assertSame(['unknown_option', 'unknown_option'], array_column($errors, 'code'));
    }

    public function testDistinctOutputRequiresAFieldAndAcceptsExistingFilters(): void
    {
        self::assertSame([], StatsTagValidationService::validationErrors([
            'id' => '25',
            'field' => 'Departement',
            'output' => 'distinct',
            'filter[field]' => 'Federation',
            'filter[value]' => 'FFC|FFV*',
            'value' => '78|60',
        ]));
        self::assertSame(['field'], array_column(StatsTagValidationService::validationErrors([
                'id' => '25',
                'output' => 'distinct',
            ]), 'parameter'));
    }

    public function testRemainingRequiresAPositiveUnquotedTargetAndAcceptsFilters(): void
    {
        $valid = TagSyntaxService::parse(
            'id=15 output=remaining target=200 filter[field]=Status filter[value]="Open|Pending"'
        );
        self::assertSame([], StatsTagValidationService::validationErrors(
            $valid['attributes'],
            0,
            $valid['quoted']
        ));

        foreach (['', '0', '-1', 'abc'] as $target) {
            self::assertSame(['target'], array_column(StatsTagValidationService::validationErrors([
                'id' => '15', 'output' => 'remaining', 'target' => $target,
            ]), 'parameter'));
        }

        $quoted = TagSyntaxService::parse('id=15 output=remaining target="200"');
        self::assertSame(
            ['target_syntax'],
            array_column(StatsTagValidationService::validationErrors(
                $quoted['attributes'],
                0,
                $quoted['quoted']
            ), 'detail')
        );
        self::assertSame(['target'], array_column(StatsTagValidationService::validationErrors([
            'id' => '15', 'output' => 'total', 'target' => '200',
        ]), 'parameter'));
    }

    public function testPercentageAndProgressSyntaxIsStrict(): void
    {
        self::assertSame([], StatsTagValidationService::validationErrors([
            'id' => '15',
            'field' => 'Civilite',
            'value' => 'H',
            'filter[field]' => 'Parcours',
            'filter[value]' => '200 km',
            'output' => 'percentage',
        ]));
        self::assertSame(['value'], array_column(StatsTagValidationService::validationErrors([
            'id' => '15', 'field' => 'Civilite', 'output' => 'percentage',
        ]), 'parameter'));
        self::assertSame([], StatsTagValidationService::validationErrors([
            'id' => '15', 'output' => 'progress', 'target' => '200',
        ]));
        self::assertSame(['target'], array_column(StatsTagValidationService::validationErrors([
            'id' => '15', 'output' => 'progress', 'target' => '0',
        ]), 'parameter'));
    }

    public function testViewNameReplacesFormNameWithoutAlias(): void
    {
        self::assertSame([], StatsTagValidationService::validationErrors([
            'id' => '15', 'output' => 'view_name',
        ]));
        self::assertSame(['output'], array_column(StatsTagValidationService::validationErrors([
            'id' => '15', 'output' => 'form_name',
        ]), 'parameter'));
        self::assertSame(['idsum_output'], array_column(StatsTagValidationService::validationErrors([
            'idsum' => '15+16', 'field' => 'Group', 'output' => 'view_name',
        ]), 'detail'));
    }

    public function testCardsAndResponsiveDimensionsAreValidatedStrictly(): void
    {
        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'v1', 'v2', 'v3', 'v4', 'v5', 'v6'] as $card) {
            self::assertSame([], StatsTagValidationService::validationErrors([
                'id' => '15', 'field' => 'Group', 'output' => 'pie',
                'card' => $card, 'width' => '350', 'height' => '280px',
            ]));
        }

        foreach (['350', '350px', '80%', '100%'] as $width) {
            self::assertSame([], StatsTagValidationService::validationErrors([
                'id' => '15', 'field' => 'Group', 'output' => 'pie', 'width' => $width,
            ]));
        }

        foreach (['280', '280px', '80%'] as $height) {
            self::assertSame([], StatsTagValidationService::validationErrors([
                'id' => '15', 'field' => 'Group', 'output' => 'bar', 'height' => $height,
            ]));
        }

        self::assertSame(
            ['card', 'width', 'height'],
            array_column(StatsTagValidationService::validationErrors([
                'id' => '15', 'field' => 'Group', 'output' => 'pie',
                'card' => 'h7', 'width' => 'calc(100%)', 'height' => '101%',
            ]), 'parameter')
        );
    }

    public function testCardWidthsAreStrictAndRequireACard(): void
    {
        foreach (['33', '66', '100'] as $width) {
            self::assertSame([], StatsTagValidationService::validationErrors([
                'id' => '15', 'field' => 'Group', 'output' => 'bar',
                'card' => 'v1', 'w' => $width,
            ]));
        }

        self::assertSame(
            ['w_requires_card'],
            array_column(StatsTagValidationService::validationErrors([
                'id' => '15', 'field' => 'Group', 'output' => 'bar', 'w' => '66',
            ]), 'detail')
        );
        self::assertSame(
            ['w'],
            array_column(StatsTagValidationService::validationErrors([
                'id' => '15', 'field' => 'Group', 'output' => 'bar', 'card' => 'v1', 'w' => '50',
            ]), 'detail')
        );

        $syntax = TagSyntaxService::parse('id=15 field=Group output=bar card=v1 w="66"');
        self::assertSame(
            ['w_syntax'],
            array_column(StatsTagValidationService::validationErrors(
                $syntax['attributes'],
                0,
                $syntax['quoted']
            ), 'detail')
        );
    }

    public function testNumericOptionsAndIdSumRequireUnquotedValues(): void
    {
        $valid = TagSyntaxService::parse('id=15 limit=10 output=total');
        self::assertSame([], StatsTagValidationService::validationErrors(
            $valid['attributes'],
            0,
            $valid['quoted']
        ));

        $invalid = TagSyntaxService::parse('idsum="15+16" field=Route output=table limit=\'10\'');
        self::assertSame(
            ['idsum_syntax', 'limit_syntax'],
            array_column(StatsTagValidationService::validationErrors(
                $invalid['attributes'],
                0,
                $invalid['quoted']
            ), 'detail')
        );
    }

    public function testPublicErrorsLinkToThePublicSyntaxPage(): void
    {
        $extension = (string) file_get_contents(
            self::ROOT . '/plugins/content/contentbuilderng_cbstats/src/Extension/ContentbuilderngStats.php'
        );
        $helpService = (string) file_get_contents(
            self::ROOT . '/site/src/Service/CbstatsHelpService.php'
        );
        $helpView = (string) file_get_contents(
            self::ROOT . '/site/src/View/Cbstatshelp/HtmlView.php'
        );

        self::assertStringContainsString('renderValidationErrors($validationErrors)', $extension);
        self::assertStringContainsString('target="_blank" rel="noopener noreferrer"', $extension);
        self::assertStringContainsString('task=cbstatshelp.display', $helpService);
        self::assertStringNotContainsString('administrator/index.php', $helpService);
        self::assertStringContainsString('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_TEXT', $helpView);
        self::assertStringContainsString('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HIDE_TEXT', $helpView);
        self::assertStringContainsString('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DEBUG_TEXT', $helpView);
    }

    public function testDescriptionsAndValidationMessagesAreCompleteInEveryLanguage(): void
    {
        foreach (['en-GB', 'fr-FR', 'de-DE'] as $locale) {
            $path = self::ROOT . '/plugins/content/contentbuilderng_cbstats/language/'
                . $locale . '/plg_content_contentbuilderng_cbstats.ini';
            $strings = parse_ini_file($path);

            self::assertIsArray($strings);
            foreach (
                [
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_VALIDATION_INTRO',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_SYNTAX_HELP',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_UNKNOWN_OPTION',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_INVALID_OPTION_VALUE',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HEADERS_LABEL',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DISTINCT_LABEL',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DISTINCT_TEXT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_REMAINING_LABEL',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_REMAINING_TEXT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_GROUPSET_LABEL',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_GROUPSET_TEXT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPECTED_TARGET',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPECTED_TARGET_OUTPUT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_EDITORIAL_CARD_LABEL',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_EDITORIAL_CARD_TEXT',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPECTED_NUMERIC_SYNTAX',
                ] as $key
            ) {
                self::assertArrayHasKey($key, $strings, $locale . ': ' . $key);
                self::assertNotSame('', $strings[$key], $locale . ': ' . $key);
            }

            self::assertStringContainsString('{CBStats id=', (string) $strings[
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_XML_DESCRIPTION'
            ]);
            self::assertStringContainsString('output=total}', (string) $strings[
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_XML_DESCRIPTION'
            ]);
            self::assertStringNotContainsString("filter[value='", (string) $strings[
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_ADD_TEXT'
            ]);
            self::assertStringContainsString('groupset', (string) $strings[
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_GROUPSET_LABEL'
            ]);
            self::assertStringContainsString('id=15', (string) $strings[
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_GROUPSET_TEXT'
            ]);

            preg_match_all('/%(?:\d+\$)?s/', (string) $strings[
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_UNKNOWN_OPTION'
            ], $unknownMatches);
            preg_match_all('/%(?:\d+\$)?s/', (string) $strings[
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_INVALID_OPTION_VALUE'
            ], $invalidMatches);
            self::assertCount(2, $unknownMatches[0], $locale);
            self::assertCount(3, $invalidMatches[0], $locale);
        }
    }
}
