<?php
/**
 * @package     ContentBuilder NG
 * @author      XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Administrator\Helper;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

final class DatabaseAuditHelper
{
    /**
     * @return array{
     *   generated_at:string,
     *   scanned_tables:int,
     *   tables:array<int,string>,
     *   duplicate_indexes:array<int,array{table:string,indexes:array<int,string>,keep:string,drop:array<int,string>}>,
     *   legacy_tables:array<int,string>,
     *   table_encoding_issues:array<int,array{table:string,collation:string,expected:string}>,
     *   column_encoding_issues:array<int,array{table:string,column:string,charset:string,collation:string}>,
     *   mixed_table_collations:array<int,array{collation:string,count:int,tables:array<int,string>}>,
     *   summary:array{
     *     duplicate_index_groups:int,
     *     duplicate_indexes_to_drop:int,
     *     legacy_tables:int,
     *     table_encoding_issues:int,
     *     column_encoding_issues:int,
     *     mixed_table_collations:int,
     *     issues_total:int
     *   },
     *   errors:array<int,string>
     * }
     */
    public static function run(): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $prefix = $db->getPrefix();
        $errors = [];

        $tables = self::collectAuditableTables($db, $errors);

        sort($tables, SORT_NATURAL | SORT_FLAG_CASE);

        [$duplicateIndexes, $duplicateErrors] = self::findDuplicateIndexes($db, $tables, $prefix);
        $errors = array_merge($errors, $duplicateErrors);

        $legacyTables = self::findLegacyContentbuilderTables($tables, $prefix);

        [$tableEncodingIssues, $columnEncodingIssues, $mixedTableCollations, $encodingErrors] =
            self::inspectEncodingAndCollation($db, $tables, $prefix);
        $errors = array_merge($errors, $encodingErrors);

        $duplicateToDrop = 0;
        foreach ($duplicateIndexes as $duplicateIndex) {
            $duplicateToDrop += count((array) ($duplicateIndex['drop'] ?? []));
        }

        $issuesTotal = count($duplicateIndexes)
            + count($legacyTables)
            + count($tableEncodingIssues)
            + count($columnEncodingIssues);

        if (count($mixedTableCollations) > 1) {
            $issuesTotal++;
        }

        return [
            'generated_at' => Factory::getDate()->toSql(),
            'scanned_tables' => count($tables),
            'tables' => array_map(
                static fn(string $tableName): string => self::toAlias($tableName, $prefix),
                $tables
            ),
            'duplicate_indexes' => $duplicateIndexes,
            'legacy_tables' => $legacyTables,
            'table_encoding_issues' => $tableEncodingIssues,
            'column_encoding_issues' => $columnEncodingIssues,
            'mixed_table_collations' => $mixedTableCollations,
            'summary' => [
                'duplicate_index_groups' => count($duplicateIndexes),
                'duplicate_indexes_to_drop' => $duplicateToDrop,
                'legacy_tables' => count($legacyTables),
                'table_encoding_issues' => count($tableEncodingIssues),
                'column_encoding_issues' => count($columnEncodingIssues),
                'mixed_table_collations' => count($mixedTableCollations),
                'issues_total' => $issuesTotal,
            ],
            'errors' => $errors,
        ];
    }

    /**
     * @param array<int,string> $errors
     * @return array<int,string>
     */
    private static function collectAuditableTables(DatabaseInterface $db, array &$errors): array
    {
        $prefix = $db->getPrefix();
        $tables = [];

        try {
            $tableList = $db->getTableList();
        } catch (\Throwable $e) {
            $errors[] = 'Could not list database tables: ' . $e->getMessage();
            return [];
        }

        foreach ((array) $tableList as $tableName) {
            $tableName = (string) $tableName;

            if ($tableName === '' || strpos($tableName, $prefix) !== 0) {
                continue;
            }

            $withoutPrefix = substr($tableName, strlen($prefix));
            if ($withoutPrefix === false || $withoutPrefix === '') {
                continue;
            }

            if (stripos($withoutPrefix, 'contentbuilder') !== false) {
                $tables[$tableName] = true;
            }
        }

        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName(['name', 'bytable']))
                ->from($db->quoteName('#__contentbuilder_ng_storages'))
                ->where($db->quoteName('name') . " <> ''");

            $db->setQuery($query);
            $storages = $db->loadAssocList() ?: [];

            foreach ($storages as $storage) {
                $storageName = strtolower(trim((string) ($storage['name'] ?? '')));
                $bytable = (int) ($storage['bytable'] ?? 0);

                if ($storageName === '' || $bytable === 1) {
                    continue;
                }

                if (!preg_match('/^[a-z0-9_]+$/', $storageName)) {
                    continue;
                }

                $physicalTable = $prefix . $storageName;
                if (in_array($physicalTable, $tableList, true)) {
                    $tables[$physicalTable] = true;
                }
            }
        } catch (\Throwable $e) {
            $errors[] = 'Could not inspect #__contentbuilder_ng_storages: ' . $e->getMessage();
        }

        return array_keys($tables);
    }

    /**
     * @param array<int,string> $tables
     * @return array{0:array<int,array{table:string,indexes:array<int,string>,keep:string,drop:array<int,string>}>,1:array<int,string>}
     */
    private static function findDuplicateIndexes(DatabaseInterface $db, array $tables, string $prefix): array
    {
        $duplicates = [];
        $errors = [];

        foreach ($tables as $tableName) {
            try {
                $indexes = self::getTableIndexes($db, $tableName);
            } catch (\Throwable $e) {
                $errors[] = 'Could not inspect indexes on ' . self::toAlias($tableName, $prefix) . ': ' . $e->getMessage();
                continue;
            }

            $signatureMap = [];
            foreach ($indexes as $indexName => $definition) {
                if (strtoupper($indexName) === 'PRIMARY') {
                    continue;
                }

                $signature = (string) ($definition['signature'] ?? '');
                if ($signature === '') {
                    continue;
                }

                $signatureMap[$signature][] = $indexName;
            }

            foreach ($signatureMap as $indexNames) {
                if (count($indexNames) < 2) {
                    continue;
                }

                sort($indexNames, SORT_NATURAL | SORT_FLAG_CASE);
                $keep = (string) array_shift($indexNames);

                $duplicates[] = [
                    'table' => self::toAlias($tableName, $prefix),
                    'indexes' => array_merge([$keep], $indexNames),
                    'keep' => $keep,
                    'drop' => $indexNames,
                ];
            }
        }

        usort(
            $duplicates,
            static fn(array $a, array $b): int => strcmp((string) ($a['table'] ?? ''), (string) ($b['table'] ?? ''))
        );

        return [$duplicates, $errors];
    }

    /**
     * @param array<int,string> $tables
     * @return array<int,string>
     */
    private static function findLegacyContentbuilderTables(array $tables, string $prefix): array
    {
        $legacy = [];

        foreach ($tables as $tableName) {
            if (strpos($tableName, $prefix) !== 0) {
                continue;
            }

            $withoutPrefix = strtolower(substr($tableName, strlen($prefix)));
            if ($withoutPrefix === false || $withoutPrefix === '') {
                continue;
            }

            if (strpos($withoutPrefix, 'contentbuilder_') === 0 && strpos($withoutPrefix, 'contentbuilder_ng_') !== 0) {
                $legacy[] = self::toAlias($tableName, $prefix);
            }
        }

        sort($legacy, SORT_NATURAL | SORT_FLAG_CASE);

        return $legacy;
    }

    /**
     * @param array<int,string> $tables
     * @return array{
     *   0:array<int,array{table:string,collation:string,expected:string}>,
     *   1:array<int,array{table:string,column:string,charset:string,collation:string}>,
     *   2:array<int,array{collation:string,count:int,tables:array<int,string>}>,
     *   3:array<int,string>
     * }
     */
    private static function inspectEncodingAndCollation(DatabaseInterface $db, array $tables, string $prefix): array
    {
        $tableIssues = [];
        $columnIssues = [];
        $mixedCollations = [];
        $errors = [];
        $tablesByCollation = [];

        if ($tables === []) {
            return [$tableIssues, $columnIssues, $mixedCollations, $errors];
        }

        $quotedTables = array_map(
            static fn(string $tableName): string => $db->quote($tableName),
            $tables
        );
        $inClause = implode(', ', $quotedTables);

        try {
            $db->setQuery(
                'SELECT TABLE_NAME, TABLE_COLLATION'
                . ' FROM information_schema.TABLES'
                . ' WHERE TABLE_SCHEMA = DATABASE()'
                . ' AND TABLE_NAME IN (' . $inClause . ')'
            );
            $tableRows = $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            $errors[] = 'Could not inspect table collations: ' . $e->getMessage();
            $tableRows = [];
        }

        $collationStats = [];

        foreach ($tableRows as $row) {
            $tableName = (string) ($row['TABLE_NAME'] ?? $row['table_name'] ?? '');
            $collation = (string) ($row['TABLE_COLLATION'] ?? $row['table_collation'] ?? '');

            if ($tableName === '') {
                continue;
            }

            if ($collation !== '') {
                $collationStats[$collation] = ($collationStats[$collation] ?? 0) + 1;
                $tablesByCollation[$collation][] = self::toAlias($tableName, $prefix);
            }

            if ($collation === '' || stripos($collation, 'utf8mb4_') !== 0) {
                $tableIssues[] = [
                    'table' => self::toAlias($tableName, $prefix),
                    'collation' => $collation,
                    'expected' => 'utf8mb4_*',
                ];
            }
        }

        arsort($collationStats);
        foreach ($collationStats as $collation => $count) {
            $collationTables = array_values(array_unique((array) ($tablesByCollation[$collation] ?? [])));
            sort($collationTables, SORT_NATURAL | SORT_FLAG_CASE);
            $mixedCollations[] = [
                'collation' => (string) $collation,
                'count' => (int) $count,
                'tables' => $collationTables,
            ];
        }

        try {
            $db->setQuery(
                'SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME'
                . ' FROM information_schema.COLUMNS'
                . ' WHERE TABLE_SCHEMA = DATABASE()'
                . ' AND TABLE_NAME IN (' . $inClause . ')'
                . ' AND COLLATION_NAME IS NOT NULL'
            );
            $columnRows = $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            $errors[] = 'Could not inspect column collations: ' . $e->getMessage();
            $columnRows = [];
        }

        foreach ($columnRows as $row) {
            $tableName = (string) ($row['TABLE_NAME'] ?? $row['table_name'] ?? '');
            $columnName = (string) ($row['COLUMN_NAME'] ?? $row['column_name'] ?? '');
            $charset = strtolower((string) ($row['CHARACTER_SET_NAME'] ?? $row['character_set_name'] ?? ''));
            $collation = (string) ($row['COLLATION_NAME'] ?? $row['collation_name'] ?? '');

            if ($tableName === '' || $columnName === '') {
                continue;
            }

            if ($charset !== 'utf8mb4' || stripos($collation, 'utf8mb4_') !== 0) {
                $columnIssues[] = [
                    'table' => self::toAlias($tableName, $prefix),
                    'column' => $columnName,
                    'charset' => $charset,
                    'collation' => $collation,
                ];
            }
        }

        usort(
            $tableIssues,
            static fn(array $a, array $b): int => strcmp((string) ($a['table'] ?? ''), (string) ($b['table'] ?? ''))
        );
        usort(
            $columnIssues,
            static fn(array $a, array $b): int => strcmp(
                ((string) ($a['table'] ?? '')) . ':' . ((string) ($a['column'] ?? '')),
                ((string) ($b['table'] ?? '')) . ':' . ((string) ($b['column'] ?? ''))
            )
        );

        return [$tableIssues, $columnIssues, $mixedCollations, $errors];
    }

    /**
     * @return array<string,array{non_unique:int,index_type:string,columns:array<int,array{name:string,sub_part:string,collation:string}>,signature:string}>
     */
    private static function getTableIndexes(DatabaseInterface $db, string $tableName): array
    {
        $tableQN = $db->quoteName($tableName);
        $db->setQuery('SHOW INDEX FROM ' . $tableQN);
        $rows = $db->loadAssocList() ?: [];
        $indexMap = [];

        foreach ($rows as $row) {
            $keyName = (string) ($row['Key_name'] ?? $row['key_name'] ?? '');
            if ($keyName === '') {
                continue;
            }

            $seqInIndex = (int) ($row['Seq_in_index'] ?? $row['seq_in_index'] ?? 0);
            if ($seqInIndex < 1) {
                $seqInIndex = count($indexMap[$keyName]['columns'] ?? []) + 1;
            }

            $columnName = strtolower((string) ($row['Column_name'] ?? $row['column_name'] ?? ''));
            if ($columnName === '') {
                $columnName = strtolower(trim((string) ($row['Expression'] ?? $row['expression'] ?? '')));
            }

            if ($columnName === '') {
                continue;
            }

            if (!isset($indexMap[$keyName])) {
                $indexMap[$keyName] = [
                    'non_unique' => (int) ($row['Non_unique'] ?? $row['non_unique'] ?? 1),
                    'index_type' => strtoupper((string) ($row['Index_type'] ?? $row['index_type'] ?? 'BTREE')),
                    'columns' => [],
                    'signature' => '',
                ];
            }

            $indexMap[$keyName]['columns'][$seqInIndex] = [
                'name' => $columnName,
                'sub_part' => (string) ($row['Sub_part'] ?? $row['sub_part'] ?? ''),
                'collation' => strtoupper((string) ($row['Collation'] ?? $row['collation'] ?? 'A')),
            ];
        }

        foreach ($indexMap as &$indexDefinition) {
            ksort($indexDefinition['columns'], SORT_NUMERIC);
            $indexDefinition['columns'] = array_values($indexDefinition['columns']);
            $indexDefinition['signature'] = self::indexDefinitionSignature($indexDefinition);
        }
        unset($indexDefinition);

        return $indexMap;
    }

    private static function indexDefinitionSignature(array $indexDefinition): string
    {
        $columnParts = [];

        foreach ((array) ($indexDefinition['columns'] ?? []) as $columnDefinition) {
            $columnParts[] = implode(':', [
                (string) ($columnDefinition['name'] ?? ''),
                (string) ($columnDefinition['sub_part'] ?? ''),
                (string) ($columnDefinition['collation'] ?? ''),
            ]);
        }

        return implode('|', [
            (string) ($indexDefinition['non_unique'] ?? 1),
            strtoupper((string) ($indexDefinition['index_type'] ?? 'BTREE')),
            implode(',', $columnParts),
        ]);
    }

    private static function toAlias(string $tableName, string $prefix): string
    {
        if ($prefix !== '' && strpos($tableName, $prefix) === 0) {
            return '#__' . substr($tableName, strlen($prefix));
        }

        return $tableName;
    }
}
