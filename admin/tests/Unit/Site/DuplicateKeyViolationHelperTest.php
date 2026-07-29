<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Site;

use CB\Component\Contentbuilderng\Site\Helper\DuplicateKeyViolationHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 4) . '/site/src/Helper/DuplicateKeyViolationHelper.php';

/**
 * This is the shared decision point behind every race-condition fallback in
 * EditModel::store()/change_list_states() and ApiController::ratePayload():
 * whether a caught exception is a benign "someone else already inserted this
 * row" race that should be swallowed, or a real failure that must propagate.
 */
final class DuplicateKeyViolationHelperTest extends TestCase
{
    #[DataProvider('duplicateKeyMessagesProvider')]
    public function testRecognizesDuplicateKeyViolationsAcrossDriversAndCasing(string $message): void
    {
        self::assertTrue(
            DuplicateKeyViolationHelper::isDuplicateKeyViolation(new \RuntimeException($message))
        );
    }

    public static function duplicateKeyMessagesProvider(): array
    {
        return [
            'mysqli style' => ["Duplicate entry '11' for key 'idx_type_reference_record'"],
            'uppercase driver message' => ['DUPLICATE ENTRY FOR KEY idx_form_record'],
            'pdo style with SQLSTATE and error code' => [
                'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry',
            ],
            'error code only, no literal wording' => [
                'SQLSTATE[23000]: Integrity constraint violation: 1062',
            ],
        ];
    }

    #[DataProvider('unrelatedMessagesProvider')]
    public function testDoesNotMisclassifyUnrelatedDatabaseErrors(string $message): void
    {
        self::assertFalse(
            DuplicateKeyViolationHelper::isDuplicateKeyViolation(new \RuntimeException($message))
        );
    }

    public static function unrelatedMessagesProvider(): array
    {
        return [
            'connection lost' => ['SQLSTATE[HY000]: General error: 2006 MySQL server has gone away'],
            'unknown column' => ["Unknown column 'record_id' in 'field list'"],
            'lock wait timeout' => ['Lock wait timeout exceeded; try restarting transaction'],
            'empty message' => [''],
        ];
    }
}
