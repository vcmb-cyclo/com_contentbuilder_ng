<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Tests\Unit\Site;

use PHPUnit\Framework\TestCase;

final class StatsServiceBreezingFormsTotalsTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testBreezingFormsTotalsJoinTheRealSourceRecordOnIdAndForm(): void
    {
        $source = (string) file_get_contents(self::ROOT . '/site/src/Service/StatsService.php');
        $method = strstr($source, 'private function joinBreezingFormsRecords', false);
        $method = strstr((string) $method, 'private function getStatsFilterPayload', true);

        self::assertStringContainsString("!== 'com_breezingformsng'", (string) $method);
        self::assertStringContainsString("quoteName('#__facileforms_records', 'bf_records')", (string) $method);
        self::assertStringContainsString("quoteName('bf_records.id') . ' = ' . \$this->db->quoteName('records.record_id')", (string) $method);
        self::assertStringContainsString("quoteName('bf_records.form') . ' = ' . \$this->db->quoteName('records.reference_id')", (string) $method);
    }

    public function testBothRecordAggregateQueriesUseTheGuardedJoin(): void
    {
        $source = (string) file_get_contents(self::ROOT . '/site/src/Service/StatsService.php');
        $payload = strstr($source, 'public function getStatsPayload', false);
        $payload = strstr((string) $payload, 'private function joinBreezingFormsRecords', true);

        self::assertSame(2, substr_count((string) $payload, '$this->joinBreezingFormsRecords($query, $formRow);'));
    }
}
