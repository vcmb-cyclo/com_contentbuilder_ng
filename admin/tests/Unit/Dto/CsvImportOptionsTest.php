<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Dto;

use CB\Component\Contentbuilderng\Administrator\Dto\CsvImportOptions;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 3) . '/src/Dto/CsvImportOptions.php';

final class CsvImportOptionsTest extends TestCase
{
    public function testNestedJformOptionsTakePrecedence(): void
    {
        $options = CsvImportOptions::fromPostData([
            'csv_import_columns' => ['0', '1'],
            'csv_delimiter' => ',',
            'jform' => [
                'csv_import_columns' => ['0'],
                'csv_delimiter' => ';',
                'csv_repair_encoding' => 'WINDOWS-1252',
                'csv_import_data' => '1',
                'csv_drop_records' => '0',
                'csv_published' => '1',
            ],
        ]);

        self::assertSame(['0'], $options->selectedColumns);
        self::assertNotContains('1', $options->selectedColumns, 'The unchecked geometry-wkt column must stay excluded.');
        self::assertSame(';', $options->delimiter);
        self::assertSame('WINDOWS-1252', $options->repairEncoding);
        self::assertTrue($options->importData);
        self::assertFalse($options->dropRecords);
        self::assertSame(1, $options->published);
    }

    public function testDefaultsMeanAllColumnsAndDataRows(): void
    {
        $options = CsvImportOptions::fromPostData(['jform' => []]);

        self::assertNull($options->selectedColumns);
        self::assertSame(',', $options->delimiter);
        self::assertTrue($options->importData);
        self::assertFalse($options->dropRecords);
        self::assertSame(0, $options->published);
    }

    public function testJoomlaHiddenAndCheckedValuesUseTheLastValue(): void
    {
        $options = CsvImportOptions::fromPostData([
            'jform' => [
                'csv_import_data' => ['0', '1'],
                'csv_drop_records' => ['0'],
            ],
        ]);

        self::assertTrue($options->importData);
        self::assertFalse($options->dropRecords);
    }
}
