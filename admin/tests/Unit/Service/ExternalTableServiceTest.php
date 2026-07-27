<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Service;

use CB\Component\Contentbuilderng\Administrator\Service\ExternalTableService;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 3) . '/src/Service/ExternalTableService.php';

final class ExternalTableServiceTest extends TestCase
{
    public function testClassifiesKnownTablesAsReadOnly(): void
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('getPrefix')->willReturn('jos_');

        $service = new ExternalTableService($db);

        self::assertTrue($service->isKnownReadOnly('jos_users'));
        self::assertTrue($service->isKnownReadOnly('jos_facileforms_records'));
        self::assertTrue($service->isKnownReadOnly('jos_breezingforms_records'));
        self::assertTrue($service->isKnownReadOnly('custom_Breezing_Archive'));
        self::assertTrue($service->isKnownReadOnly('jos_hikashop_product'));
        self::assertTrue($service->isKnownReadOnly('jos_virtuemart_products'));
        self::assertTrue($service->isKnownReadOnly('jos_rsform_submissions'));
        self::assertTrue($service->isKnownReadOnly('jos_sppagebuilder'));
        self::assertFalse($service->isKnownReadOnly('external_catalogue'));
        self::assertSame(2, $service->getBytableMode('jos_users'));
        self::assertSame(2, $service->getBytableMode('jos_facileforms_records'));
        self::assertSame(1, $service->getBytableMode('external_catalogue'));
        self::assertSame('joomla', $service->getSourceType('jos_users'));
        self::assertSame('Joomla', $service->getSourceLabel('jos_user_keys'));
        self::assertSame('breezingforms', $service->getSourceType('jos_facileforms_records'));
        self::assertSame('joomla-extension', $service->getSourceType('jos_hikashop_product'));
        self::assertSame('external', $service->getSourceType('external_catalogue'));
        self::assertSame('Joomla', $service->getSourceLabel('jos_users'));
        self::assertSame('BreezingForms', $service->getSourceLabel('jos_facileforms_records'));
        self::assertSame('HikaShop', $service->getSourceLabel('jos_hikashop_product'));
        self::assertSame('VirtueMart', $service->getSourceLabel('jos_virtuemart_products'));
        self::assertSame('', $service->getSourceLabel('external_catalogue'));
    }

    public function testFindsMissingSystemColumns(): void
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('getPrefix')->willReturn('jos_');

        $missing = (new ExternalTableService($db))->getMissingSystemColumns(['id', 'title']);

        self::assertNotContains('id', $missing);
        self::assertContains('storage_id', $missing);
    }

    public function testTablesWithoutAnIdColumnAreNeverSelectable(): void
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('getPrefix')->willReturn('jos_');
        $db->method('getTableList')->willReturn(['jos_user_profiles', 'jos_external_catalogue']);
        $db->method('getTableColumns')->willReturnMap([
            ['jos_user_profiles', false, ['user_id' => 'int', 'profile_key' => 'varchar']],
            ['jos_external_catalogue', false, ['id' => 'int', 'title' => 'varchar']],
        ]);

        $service = new ExternalTableService($db);

        self::assertFalse($service->isSelectable('jos_user_profiles'));
        self::assertTrue($service->isSelectable('jos_external_catalogue'));
        self::assertSame(['jos_external_catalogue'], $service->getSelectableTables());
    }

    public function testIdColumnLookupUsesASingleQueryForTheWholeSchema(): void
    {
        $db = new ExternalTableLookupDatabase(
            ['jos_user_profiles', 'jos_external_catalogue', 'jos_other'],
            ['jos_external_catalogue', 'jos_other']
        );

        $service = new ExternalTableService($db);

        self::assertSame(['jos_external_catalogue', 'jos_other'], $service->getSelectableTables());
        self::assertFalse($service->isSelectable('jos_user_profiles'));
        self::assertTrue($service->isSelectable('jos_other'));

        // One INFORMATION_SCHEMA query for the whole schema, memoised across
        // every call, and no per-table SHOW FULL COLUMNS fallback.
        self::assertSame(1, $db->lookupQueries);
        self::assertSame(0, $db->tableColumnsCalls);
    }

    public function testFallsBackToPerTableLookupWhenInformationSchemaIsUnavailable(): void
    {
        $db = new ExternalTableLookupDatabase(
            ['jos_user_profiles', 'jos_external_catalogue'],
            ['jos_external_catalogue'],
            true
        );

        $service = new ExternalTableService($db);

        // Degraded mode must still classify correctly rather than emptying the
        // selector.
        self::assertSame(['jos_external_catalogue'], $service->getSelectableTables());
        self::assertTrue($db->tableColumnsCalls > 0);
    }
}

final class ExternalTableLookupDatabase implements DatabaseInterface
{
    public int $lookupQueries = 0;
    public int $tableColumnsCalls = 0;

    /**
     * @param list<string> $tables
     * @param list<string> $tablesWithId
     */
    public function __construct(
        private readonly array $tables,
        private readonly array $tablesWithId,
        private readonly bool $failLookup = false
    ) {
    }

    public function getQuery(bool $new = false): ExternalTableLookupQuery
    {
        if ($this->failLookup) {
            throw new \RuntimeException('INFORMATION_SCHEMA unavailable');
        }

        return new ExternalTableLookupQuery();
    }

    public function getPrefix(): string
    {
        return 'jos_';
    }

    public function getTableList(): array
    {
        return $this->tables;
    }

    public function getTableColumns(string $table, bool $type = true): array
    {
        $this->tableColumnsCalls++;

        return in_array($table, $this->tablesWithId, true) ? ['id' => 'int'] : ['user_id' => 'int'];
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

    public function setQuery(QueryInterface|string $query): void
    {
        $this->lookupQueries++;
    }

    public function execute(): void
    {
    }

    /**
     * @return list<string>
     */
    public function loadColumn(): array
    {
        return $this->tablesWithId;
    }
}

final class ExternalTableLookupQuery implements QueryInterface
{
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
}
