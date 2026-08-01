<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Helper;

use CB\Component\Contentbuilderng\Administrator\Helper\StorageColumnTypeHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once \dirname(__DIR__, 3) . '/src/Helper/StorageColumnTypeHelper.php';

final class StorageColumnTypeHelperTest extends TestCase
{
    public function testProvidesTranslatedOptionsAndLabels(): void
    {
        self::assertSame(
            'COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_DECIMAL',
            StorageColumnTypeHelper::options()['decimal']
        );
        self::assertSame(
            'COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_BOOLEAN',
            StorageColumnTypeHelper::label(' BOOLEAN ')
        );
    }

    public function testNormalizesUnknownAndEmptyTypesToText(): void
    {
        self::assertSame('text', StorageColumnTypeHelper::normalize(null));
        self::assertSame('text', StorageColumnTypeHelper::normalize('unknown'));
        self::assertSame('varchar', StorageColumnTypeHelper::normalize(' VARCHAR '));
    }

    public function testMapsStorageTypesToNativeEditableControls(): void
    {
        self::assertSame('text', StorageColumnTypeHelper::editableElementType('text'));
        self::assertSame('text', StorageColumnTypeHelper::editableElementType('varchar'));
        self::assertSame('number', StorageColumnTypeHelper::editableElementType('int'));
        self::assertSame('decimal', StorageColumnTypeHelper::editableElementType('decimal'));
        self::assertSame('calendar', StorageColumnTypeHelper::editableElementType('date'));
        self::assertSame('datetime', StorageColumnTypeHelper::editableElementType('datetime'));
        self::assertSame('boolean', StorageColumnTypeHelper::editableElementType('boolean'));

        foreach (['', 'text', 'number', 'decimal', 'calendar', 'datetime', 'boolean'] as $managedType) {
            self::assertTrue(StorageColumnTypeHelper::isStorageManagedEditableType($managedType));
        }

        self::assertFalse(StorageColumnTypeHelper::isStorageManagedEditableType('upload'));
    }

    /**
     * @return array<string,array{0:string,1:string}>
     */
    public static function sqlDefinitionProvider(): array
    {
        return [
            'text' => ['text', 'TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL'],
            'varchar' => ['varchar', 'VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL'],
            'integer' => ['int', 'INT NULL'],
            'decimal' => ['decimal', 'DECIMAL(15,4) NULL'],
            'date' => ['date', 'DATE NULL'],
            'datetime' => ['datetime', 'DATETIME NULL'],
            'boolean' => ['boolean', 'TINYINT(1) NULL'],
        ];
    }

    #[DataProvider('sqlDefinitionProvider')]
    public function testBuildsSqlDefinition(string $type, string $expected): void
    {
        self::assertSame($expected, StorageColumnTypeHelper::sqlDefinition($type));
    }

    /**
     * @return array<string,array{0:string,1:mixed,2:bool}>
     */
    public static function physicalTypeProvider(): array
    {
        return [
            'varchar' => ['varchar', ['Type' => 'varchar(255)'], true],
            'integer object' => ['int', (object) ['type' => 'INT(11) unsigned'], true],
            'decimal' => ['decimal', 'decimal(15,4)', true],
            'date is not datetime' => ['date', 'datetime', false],
            'datetime' => ['datetime', 'DATETIME', true],
            'boolean tinyint' => ['boolean', 'tinyint(1)', true],
            'boolean alias' => ['boolean', 'bool', true],
            'long text' => ['text', 'longtext', true],
            'empty definition' => ['text', [], false],
            'wrong type' => ['int', 'bigint', false],
        ];
    }

    #[DataProvider('physicalTypeProvider')]
    public function testMatchesPhysicalTypes(string $expectedType, mixed $definition, bool $expected): void
    {
        self::assertSame(
            $expected,
            StorageColumnTypeHelper::physicalTypeMatches($expectedType, $definition)
        );
    }

    public function testExtractsPhysicalTypeFromSupportedDefinitions(): void
    {
        self::assertSame('varchar(64)', StorageColumnTypeHelper::extractPhysicalType(['Type' => ' VARCHAR(64) NULL ']));
        self::assertSame('int(11)', StorageColumnTypeHelper::extractPhysicalType((object) ['type' => 'INT(11) unsigned']));
        self::assertSame('', StorageColumnTypeHelper::extractPhysicalType(''));
    }

    public function testMatchesPhysicalNullabilityFromMySqlDefinitions(): void
    {
        self::assertTrue(StorageColumnTypeHelper::physicalNullabilityMatches(true, ['Null' => 'NO']));
        self::assertFalse(StorageColumnTypeHelper::physicalNullabilityMatches(true, ['Null' => 'YES']));
        self::assertTrue(StorageColumnTypeHelper::physicalNullabilityMatches(false, (object) ['Null' => 'YES']));
        self::assertFalse(StorageColumnTypeHelper::physicalNullabilityMatches(false, (object) ['Null' => 'NO']));
        self::assertTrue(StorageColumnTypeHelper::physicalNullabilityMatches(true, 'INT NOT NULL'));
        self::assertTrue(StorageColumnTypeHelper::physicalNullabilityMatches(false, 'INT NULL'));
        self::assertTrue(StorageColumnTypeHelper::physicalNullabilityMatches(true, 'INT'));
        self::assertSame('int(11) NOT NULL', StorageColumnTypeHelper::describePhysicalDefinition([
            'Type' => 'INT(11)',
            'Null' => 'NO',
        ]));
        self::assertSame('varchar(255) NULL', StorageColumnTypeHelper::describePhysicalDefinition((object) [
            'Type' => 'VARCHAR(255)',
            'Null' => 'YES',
        ]));
    }
}
