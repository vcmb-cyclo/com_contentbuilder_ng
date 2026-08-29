<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Plugin;

use CB\Component\Contentbuilderng\Site\Service\StatsService;
use CB\Plugin\Content\ContentbuilderngStats\Service\ManualExportService;
use CB\Plugin\Content\ContentbuilderngStats\Service\TableHeaderService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CbStatsTableHeaderServiceTest extends TestCase
{
    public function testDefaultTableLabelsUseFieldAndCount(): void
    {
        self::assertSame(
            ['category' => 'Dpt', 'value' => 'Count'],
            TableHeaderService::resolve(
                ['requested' => 'Dpt', 'label' => 'Dpt'],
                StatsService::parseFieldStatsLabels(''),
                'Value',
                'Count'
            )
        );
    }

    public function testAllPresentationLabelsAreParsed(): void
    {
        $labels = StatsService::parseFieldStatsLabels(
            ' title = Groupes VCMB ; category = Groupe ; value = Inscrits ; total = Total des groupes '
        );

        self::assertSame([
            'title' => 'Groupes VCMB',
            'category' => 'Groupe',
            'value' => 'Inscrits',
            'total' => 'Total des groupes',
        ], $labels);
        self::assertSame(
            ['category' => 'Groupe', 'value' => 'Inscrits'],
            TableHeaderService::resolve(['requested' => 'Dpt', 'label' => 'Département'], $labels, 'Value', 'Count')
        );
    }

    #[DataProvider('invalidLabelsProvider')]
    public function testInvalidLabelsAreRejected(string $labels): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(StatsService::CBSTATS_ERROR_INVALID_LABELS);
        StatsService::parseFieldStatsLabels($labels);
    }

    public static function invalidLabelsProvider(): array
    {
        return [
            'unknown key' => ['header=Groupe'],
            'missing value' => ['total='],
            'missing equals' => ['total'],
            'duplicate key' => ['total=One;total=Two'],
        ];
    }

    public function testManualExportPreservesLabelsForEveryVisualOutput(): void
    {
        $labels = 'title=Groupes VCMB;category=Groupe;value=Inscrits;total=Total des groupes';

        foreach (['table', 'pie', 'bar', 'histogram', 'line', 'radar'] as $output) {
            $syntax = ManualExportService::buildSyntax(
                [['label' => 'Yvelines', 'value' => 1]],
                $output,
                $labels
            );

            self::assertStringContainsString(' labels="' . $labels . '"', $syntax);
            self::assertStringNotContainsString(' title=', $syntax);
            self::assertStringNotContainsString(' headers=', $syntax);
        }
    }
}
