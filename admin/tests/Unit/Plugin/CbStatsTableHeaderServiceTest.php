<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Plugin;

use CB\Component\Contentbuilderng\Site\Service\StatsService;
use CB\Plugin\Content\ContentbuilderngStats\Service\ManualExportService;
use CB\Plugin\Content\ContentbuilderngStats\Service\TableHeaderService;
use PHPUnit\Framework\TestCase;

final class CbStatsTableHeaderServiceTest extends TestCase
{
    public function testMissingHeadersPreserveRc94Defaults(): void
    {
        self::assertSame(
            ['label' => 'Dpt', 'total' => 'Total'],
            TableHeaderService::resolve(
                ['requested' => 'Dpt', 'label' => 'Dpt'],
                StatsService::parseFieldStatsHeaders(''),
                'Value',
                'Total'
            )
        );
    }

    public function testOneHeaderCanBeReplaced(): void
    {
        self::assertSame(
            ['label' => 'Département', 'total' => 'Total'],
            $this->resolve('Dpt=Département')
        );
    }

    public function testTwoHeadersCanBeReplacedWithWhitespace(): void
    {
        self::assertSame(
            ['label' => 'Département', 'total' => 'Qté'],
            $this->resolve(' Dpt = Département ; Total = Qté ')
        );
    }

    public function testUnknownAndEmptyMappingsAreIgnored(): void
    {
        self::assertSame(
            ['label' => 'Dpt', 'total' => 'Qté'],
            $this->resolve('Inconnue=Test;Dpt=;Total=Qté')
        );
    }

    public function testRequestedFieldKeyCanBeUsedWhenDisplayedLabelDiffers(): void
    {
        self::assertSame(
            ['label' => 'Département', 'total' => 'Nombre'],
            TableHeaderService::resolve(
                ['requested' => 'Dpt', 'label' => 'Département source'],
                StatsService::parseFieldStatsHeaders('Dpt=Département;Total=Nombre'),
                'Value',
                'Total'
            )
        );
    }

    public function testUtf8AndFirstEqualsSignArePreserved(): void
    {
        self::assertSame(
            ['Dpt' => 'Département', 'Total' => 'Quantité d’inscrits = validée'],
            StatsService::parseFieldStatsHeaders(
                'Dpt=Département;Total=Quantité d’inscrits = validée'
            )
        );
    }

    public function testHeadersDoNotChangeCategoryTitles(): void
    {
        $items = StatsService::normalizeFieldStats(
            ['78' => 1],
            titles: StatsService::parseFieldStatsTitles('78=Yvelines')
        );

        self::assertSame([['label' => 'Yvelines', 'value' => 1]], $items);
        self::assertSame(
            ['label' => 'Département', 'total' => 'Qté'],
            $this->resolve('Dpt=Département;Total=Qté')
        );
    }

    public function testManualExportPreservesHeadersForEveryVisualOutput(): void
    {
        foreach (['table', 'pie', 'bar'] as $output) {
            $syntax = ManualExportService::buildSyntax(
                [['label' => 'Yvelines', 'value' => 1]],
                $output,
                '👥 Total des inscrits',
                '',
                'Dpt=Département;Total=Qté'
            );

            self::assertStringContainsString(
                ' headers="Dpt=Département;Total=Qté"',
                $syntax
            );
            self::assertStringContainsString(' title="👥 Total des inscrits"', $syntax);
        }
    }

    public function testManualFieldKeyKeepsExportedHeaderMappingEffective(): void
    {
        self::assertSame(
            'Dpt',
            TableHeaderService::resolveManualFieldKey(
                StatsService::parseFieldStatsHeaders('Dpt=Département;Total=Qté'),
                'Value',
                'Total'
            )
        );
    }

    public function testPieAndBarLegendsRemainHeaderFree(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/plugins/content/contentbuilderng_cbstats/src/Extension/ContentbuilderngStats.php'
        );
        $chartDetails = substr(
            $source,
            strpos($source, 'private function renderChartDetails'),
            strpos($source, 'private function formatNumber')
                - strpos($source, 'private function renderChartDetails')
        );

        self::assertStringNotContainsString('TableHeaderService::resolve', $chartDetails);
        self::assertStringContainsString('cbstats-pie-legend', $chartDetails);
    }

    /** @return array{label: string, total: string} */
    private function resolve(string $headers): array
    {
        return TableHeaderService::resolve(
            ['requested' => 'Dpt', 'label' => 'Dpt'],
            StatsService::parseFieldStatsHeaders($headers),
            'Value',
            'Total'
        );
    }
}
