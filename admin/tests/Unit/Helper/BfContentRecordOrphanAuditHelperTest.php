<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;

final class BfContentRecordOrphanAuditHelperTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testAuditUsesReadOnlyLeftJoinWithBothBreezingFormsKeys(): void
    {
        $source = (string) file_get_contents(self::ROOT . '/admin/src/Helper/Audit/BfContentRecordOrphanAuditHelper.php');
        $inspect = strstr($source, 'public static function inspect', false);
        $inspect = strstr((string) $inspect, 'public static function repair', true);

        self::assertMatchesRegularExpression("/->join\\(\\R\\s+'LEFT'/", (string) $inspect);
        self::assertStringContainsString("quoteName('bf_records.id') . ' = ' . \$db->quoteName('records.record_id')", (string) $inspect);
        self::assertStringContainsString("quoteName('bf_records.form') . ' = ' . \$db->quoteName('records.reference_id')", (string) $inspect);
        self::assertStringNotContainsString('->delete(', (string) $inspect);
        self::assertStringNotContainsString('->update(', (string) $inspect);
    }

    public function testRepairRevalidatesAndDeletesOnlyExactContentBuilderRows(): void
    {
        $source = (string) file_get_contents(self::ROOT . '/admin/src/Helper/Audit/BfContentRecordOrphanAuditHelper.php');
        $repair = strstr($source, 'public static function repair', false);

        self::assertStringContainsString("->from(\$db->quoteName('#__facileforms_records', 'bf_records'))", (string) $repair);
        self::assertStringContainsString("->where(\$db->quoteName('bf_records.id') . ' = ' . \$recordId)", (string) $repair);
        self::assertStringContainsString("->where(\$db->quoteName('bf_records.form') . ' = ' . \$formId)", (string) $repair);
        self::assertStringContainsString("->delete(\$db->quoteName('#__contentbuilderng_records'))", (string) $repair);
        self::assertStringContainsString("->where('NOT EXISTS (' . (string) \$sourceExists . ')')", (string) $repair);
        self::assertStringNotContainsString("->delete(\$db->quoteName('#__facileforms_records'))", (string) $repair);
        self::assertStringNotContainsString('#__contentbuilderng_articles', (string) $repair);
        self::assertStringNotContainsString('#__contentbuilderng_list_records', (string) $repair);
    }

    public function testRepairIsTransactionalAndIdempotentByConstruction(): void
    {
        $source = (string) file_get_contents(self::ROOT . '/admin/src/Helper/Audit/BfContentRecordOrphanAuditHelper.php');

        self::assertStringContainsString('$db->transactionStart()', $source);
        self::assertStringContainsString('$db->transactionCommit()', $source);
        self::assertStringContainsString('$db->transactionRollback()', $source);
        self::assertStringContainsString("if ((int) \$db->getAffectedRows() === 1)", $source);
    }
}
