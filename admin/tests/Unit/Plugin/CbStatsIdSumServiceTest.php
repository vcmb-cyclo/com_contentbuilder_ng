<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Plugin;

use CB\Component\Contentbuilderng\Site\Service\StatsService;
use CB\Plugin\Content\ContentbuilderngStats\Service\IdSumException;
use CB\Plugin\Content\ContentbuilderngStats\Service\IdSumService;
use CB\Plugin\Content\ContentbuilderngStats\Service\TagSyntaxService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CbStatsIdSumServiceTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testTwoAndFiveViewIdentifiersAreAccepted(): void
    {
        self::assertSame([25, 27], IdSumService::parseIds('25+27'));
        self::assertSame([31, 32, 33, 34, 35], IdSumService::parseIds('31+32+33+34+35'));
    }

    #[DataProvider('invalidIdSumProvider')]
    public function testInvalidIdSumIsRejected(string $value, int $code): void
    {
        try {
            IdSumService::parseIds($value);
            self::fail('An invalid idsum value was accepted.');
        } catch (IdSumException $exception) {
            self::assertSame($code, $exception->getCode());
        }
    }

    public static function invalidIdSumProvider(): array
    {
        return [
            'one identifier' => ['25', IdSumException::TOO_FEW],
            'six identifiers' => ['1+2+3+4+5+6', IdSumException::TOO_MANY],
            'non numeric identifier' => ['25+abc', IdSumException::INVALID_ID],
            'zero identifier' => ['25+0', IdSumException::INVALID_ID],
            'duplicate identifier' => ['25+27+25', IdSumException::DUPLICATE_ID],
        ];
    }

    public function testIdAndIdSumCannotBeUsedTogether(): void
    {
        $this->expectException(IdSumException::class);
        $this->expectExceptionCode(IdSumException::CONFLICT);

        IdSumService::resolveSourceIds('25', '25+27');
    }

    public function testTwoViewsAreMergedByExactExistingLabels(): void
    {
        $merged = IdSumService::mergePayloads([
            $this->payload(['Femmes' => 12, 'Hommes' => 38]),
            $this->payload(['Femmes' => 15, 'Hommes' => 41]),
        ]);

        self::assertSame(['Femmes' => 27, 'Hommes' => 79], $merged['field']['values']);
        self::assertSame(106, $merged['field']['total']);
        self::assertSame(106, $merged['records']['total']);
    }

    public function testValuePresentInOneViewIsPreserved(): void
    {
        $merged = IdSumService::mergePayloads([
            $this->payload(['FFVélo' => 40, 'UFOLEP' => 5]),
            $this->payload(['FFVélo' => 30, 'FSGT' => 4]),
        ]);

        self::assertSame(['FFVélo' => 70, 'UFOLEP' => 5, 'FSGT' => 4], $merged['field']['values']);
        self::assertSame(3, $merged['field']['distinct']);
    }

    public function testFiveViewsProduceCorrectTotal(): void
    {
        $merged = IdSumService::mergePayloads([
            $this->payload(['A' => 1]),
            $this->payload(['A' => 2, 'B' => 3]),
            $this->payload(['A' => 4]),
            $this->payload(['B' => 5]),
            $this->payload(['C' => 6]),
        ]);

        self::assertSame(['A' => 7, 'B' => 8, 'C' => 6], $merged['field']['values']);
        self::assertSame(21, $merged['records']['total']);
    }

    public function testRecordTotalDoesNotUseGroupedRangeOrFieldSums(): void
    {
        $left = $this->payload(['18' => 3, '30' => 2]);
        $right = $this->payload(['40' => 4]);
        $left['records']['total'] = 8;
        $right['records']['total'] = 7;

        $merged = IdSumService::mergePayloads([$left, $right]);

        self::assertSame(15, $merged['records']['total']);
        self::assertSame(9, $merged['field']['total']);
    }

    public function testAddTitlesAndSortAreAppliedAfterMerge(): void
    {
        $merged = IdSumService::mergePayloads([
            $this->payload(['A' => 2, 'B' => 5]),
            $this->payload(['A' => 4, 'B' => 1]),
        ]);

        self::assertSame([
            ['label' => 'Groupe B', 'value' => 6],
            ['label' => 'Groupe A', 'value' => 9],
        ], StatsService::normalizeFieldStats(
            $merged['field']['values'],
            'value',
            'asc',
            'fr-FR',
            ['A' => 3],
            ['A' => 'Groupe A', 'B' => 'Groupe B']
        ));
    }

    public function testFiltersArePassedUnchangedToEveryViewLoader(): void
    {
        $options = [
            'field' => 'Route',
            'filter' => ['field' => 'Fédération', 'value' => 'FFV*', 'values' => ['FFV*']],
        ];
        $calls = [];

        IdSumService::collectPayloads(
            [25, 27],
            static function (int $id, array $received) use (&$calls): array {
                $calls[] = [$id, $received];

                return ['field' => ['values' => []]];
            },
            $options
        );

        self::assertSame([[25, $options], [27, $options]], $calls);
    }

    #[DataProvider('loaderFailureProvider')]
    public function testViewAndFieldFailuresAreNotHidden(int $code): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode($code);

        IdSumService::collectPayloads(
            [25, 999],
            static function (int $id) use ($code): array {
                if ($id === 999) {
                    throw new \RuntimeException('Unavailable view or field', $code);
                }

                return ['field' => ['values' => ['A' => 1]]];
            },
            ['field' => 'Route']
        );
    }

    public static function loaderFailureProvider(): array
    {
        return [
            'missing view' => [404],
            'missing field' => [403],
        ];
    }

    public function testHistoricalIdSyntaxIsUnchanged(): void
    {
        $attributes = TagSyntaxService::parseAttributes('id=25 field=Route output=table');

        self::assertSame([25], IdSumService::resolveSourceIds((string) $attributes['id'], '', 0));
        self::assertSame('Route', $attributes['field']);
        self::assertSame('table', $attributes['output']);
    }

    public function testIntegratedHelpAndCbApiDocumentIdSumInAllLanguages(): void
    {
        foreach (['en-GB', 'fr-FR', 'de-DE'] as $locale) {
            $pluginStrings = parse_ini_file(
                self::ROOT . '/plugins/content/contentbuilderng_cbstats/language/'
                . $locale . '/plg_content_contentbuilderng_cbstats.ini'
            );
            $adminStrings = parse_ini_file(
                self::ROOT . '/admin/language/' . $locale . '/com_contentbuilderng.ini'
            );

            self::assertIsArray($pluginStrings);
            self::assertIsArray($adminStrings);
            self::assertArrayHasKey('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_DEBUG_IDSUM_DUPLICATE', $pluginStrings);
            self::assertStringContainsString(
                'idsum=25+27',
                (string) $pluginStrings['PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_TEXT']
            );
            self::assertArrayHasKey('COM_CONTENTBUILDERNG_API_CONTENT_PLUGIN_IDSUM_HINT', $adminStrings);
            self::assertStringContainsString(
                'idsum=31+32+33+34+35',
                (string) $adminStrings['COM_CONTENTBUILDERNG_API_CONTENT_PLUGIN_IDSUM_HINT']
            );
        }

        $apiLayout = (string) file_get_contents(self::ROOT . '/admin/layouts/form/api_tab.php');
        self::assertStringContainsString('COM_CONTENTBUILDERNG_API_OPEN_CBSTATS_HELP', $apiLayout);
    }

    private function payload(array $values): array
    {
        return [
            'records' => ['total' => array_sum($values)],
            'field' => [
                'requested' => 'Field',
                'label' => 'Field',
                'values' => $values,
            ],
        ];
    }
}
