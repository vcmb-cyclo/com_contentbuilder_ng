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
            'ranges' => 'young-old',
            'typo' => 'value',
        ]);

        self::assertSame(
            [
                'typo', 'id', 'field', 'sort', 'dir', 'limit', 'hide',
                'export', 'background', 'headers', 'add', 'titles', 'ranges',
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
        ]));
        self::assertSame([], StatsTagValidationService::validationErrors([
            'source' => 'manual',
            'output' => 'pie',
            'values' => 'Short=12;Long=8',
            'sort' => 'label',
            'dir' => 'asc',
        ]));
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
            foreach ([
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_VALIDATION_INTRO',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_SYNTAX_HELP',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_UNKNOWN_OPTION',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_INVALID_OPTION_VALUE',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HEADERS_LABEL',
                'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_EXPECTED_NUMERIC_SYNTAX',
            ] as $key) {
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
