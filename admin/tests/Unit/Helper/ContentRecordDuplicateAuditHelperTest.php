<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Helper;

use CB\Component\Contentbuilderng\Administrator\Helper\Audit\ContentRecordDuplicateAuditHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 3) . '/src/Helper/Audit/ContentRecordDuplicateAuditHelper.php';

final class ContentRecordDuplicateAuditHelperTest extends TestCase
{
    private const DUPLICATE_ROWS = [
        ['id' => 8286, 'type' => 'com_breezingformsng', 'reference_id' => 11, 'record_id' => 8267],
        ['id' => 8287, 'type' => 'com_breezingformsng', 'reference_id' => 11, 'record_id' => 8267],
        ['id' => 9001, 'type' => 'com_breezingformsng', 'reference_id' => 11, 'record_id' => 9000],
    ];

    public function testInspectGroupsDuplicateRowsAndKeepsTheOldestId(): void
    {
        [$issues, $errors] = ContentRecordDuplicateAuditHelper::inspect(
            new ContentRecordDuplicateDatabase(self::DUPLICATE_ROWS)
        );

        self::assertSame([], $errors);
        self::assertCount(1, $issues);

        $group = $issues[0];
        self::assertSame('com_breezingformsng', $group['type']);
        self::assertSame(11, $group['reference_id']);
        self::assertSame(8267, $group['record_id']);
        self::assertSame(2, $group['count']);
        self::assertSame(8286, $group['keep_id']);
        self::assertSame([8287], $group['duplicate_ids']);
    }

    public function testInspectIgnoresInvalidRowsAndRowsWithoutDuplicates(): void
    {
        [$issues, $errors] = ContentRecordDuplicateAuditHelper::inspect(
            new ContentRecordDuplicateDatabase([
                ['id' => 1, 'type' => 'com_contentbuilderng', 'reference_id' => 3, 'record_id' => 42],
                ['id' => 0, 'type' => 'com_contentbuilderng', 'reference_id' => 3, 'record_id' => 43],
                'invalid row',
            ])
        );

        self::assertSame([], $errors);
        self::assertSame([], $issues);
    }

    public function testInspectReturnsWarningWhenDatabaseReadFails(): void
    {
        [$issues, $errors] = ContentRecordDuplicateAuditHelper::inspect(
            new ContentRecordDuplicateDatabase([], false, true)
        );

        self::assertSame([], $issues);
        self::assertStringContainsString('Could not inspect', $errors[0]);
    }

    public function testRepairHasNothingToDoWithoutDuplicateGroups(): void
    {
        $summary = ContentRecordDuplicateAuditHelper::repair(
            new ContentRecordDuplicateDatabase([
                ['id' => 1, 'type' => 'com_contentbuilderng', 'reference_id' => 3, 'record_id' => 42],
            ], true)
        );

        self::assertSame(0, $summary['groups']);
        self::assertSame(0, $summary['rows_removed']);
        self::assertSame(0, $summary['errors']);
        self::assertSame([], $summary['items']);
    }

    public function testRepairRemovesDuplicateRowsAndKeepsTheOldestId(): void
    {
        $db = new ContentRecordDuplicateDatabase(self::DUPLICATE_ROWS, true, false, 1);

        $summary = ContentRecordDuplicateAuditHelper::repair($db);

        self::assertSame(3, $summary['scanned']);
        self::assertSame(1, $summary['groups']);
        self::assertSame(1, $summary['rows_removed']);
        self::assertSame(0, $summary['errors']);
        self::assertSame('repaired', $summary['items'][0]['status']);
        self::assertSame(8286, $summary['items'][0]['keep_id']);
        self::assertSame([8287], $summary['items'][0]['removed_ids']);
    }

    public function testRepairReportsDeleteFailureForAGroup(): void
    {
        $db = new ContentRecordDuplicateDatabase(self::DUPLICATE_ROWS, true, false, 1, true);

        $summary = ContentRecordDuplicateAuditHelper::repair($db);

        self::assertSame(0, $summary['rows_removed']);
        self::assertSame(1, $summary['errors']);
        self::assertSame('error', $summary['items'][0]['status']);
        self::assertStringContainsString('Delete failed', $summary['items'][0]['error']);
    }
}

final class ContentRecordDuplicateDatabase implements DatabaseInterface
{
    public int $executeCount = 0;

    /**
     * @param array<int,mixed> $rows
     */
    public function __construct(
        private readonly array $rows,
        private readonly bool $withCount = false,
        private readonly bool $failRead = false,
        private readonly int $affectedRows = 0,
        private readonly bool $failWrite = false
    ) {
    }

    public function getQuery(bool $new = false): ContentRecordDuplicateQuery
    {
        return new ContentRecordDuplicateQuery();
    }

    public function getPrefix(): string
    {
        return 'joom_';
    }

    public function getTableColumns(string $table, bool $type = true): array
    {
        return [];
    }

    public function getTableList(): array
    {
        return [];
    }

    public function quoteName(array|string $name, array|string|null $as = null): array|string
    {
        if (is_array($name)) {
            return array_map(fn(string $item): string => (string) $this->quoteName($item), $name);
        }

        return '`' . $name . '`';
    }

    public function quote(mixed $value): string
    {
        return "'" . str_replace("'", "''", (string) $value) . "'";
    }

    public function setQuery(QueryInterface|string $query): self
    {
        if ($this->failWrite && $query instanceof ContentRecordDuplicateQuery && $query->isDelete()) {
            throw new \RuntimeException('Delete failed');
        }

        return $this;
    }

    public function execute(): void
    {
        $this->executeCount++;
    }

    /**
     * @return array<int,mixed>
     */
    public function loadAssocList(): array
    {
        if ($this->failRead) {
            throw new \RuntimeException('Database unavailable');
        }

        return $this->rows;
    }

    public function loadResult(): mixed
    {
        return $this->withCount ? count($this->rows) : null;
    }

    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }
}

final class ContentRecordDuplicateQuery implements QueryInterface
{
    private bool $isDelete = false;

    public function select(array|string $columns): self
    {
        return $this;
    }

    public function from(string $table): self
    {
        return $this;
    }

    public function where(string $condition): self
    {
        return $this;
    }

    public function order(string $ordering): self
    {
        return $this;
    }

    public function delete(string $table): self
    {
        $this->isDelete = true;

        return $this;
    }

    public function isDelete(): bool
    {
        return $this->isDelete;
    }
}
