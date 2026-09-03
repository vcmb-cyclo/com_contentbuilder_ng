<?php

/**
 * @package     ContentBuilderNG
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace CB\Component\Contentbuilderng\Administrator\Helper\Audit;

\defined('_JEXEC') or die('Restricted access');

use Joomla\Database\DatabaseInterface;

final class StorageIndexNamingAuditHelper
{
    /**
     * @return array{0:array<int,array<string,mixed>>,1:array<int,string>}
     */
    public static function inspect(DatabaseInterface $db): array
    {
        $issues = [];
        $errors = [];
        $prefix = $db->getPrefix();
        $legacyColumns = ['storage_id', 'user_id', 'created', 'modified_user_id', 'modified'];

        try {
            $tableNames = [];
            foreach ((array) $db->getTableList() as $tableName) {
                $tableNames[strtolower((string) $tableName)] = (string) $tableName;
            }
        } catch (\Throwable $e) {
            return [[], ['Could not list database tables for storage index naming audit: ' . $e->getMessage()]];
        }

        try {
            $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName(['id', 'name', 'title']))
                    ->from($db->quoteName('#__contentbuilderng_storages'))
                    ->where($db->quoteName('name') . " <> ''")
                    ->where($db->quoteName('bytable') . ' = 0')
                    ->order($db->quoteName('id') . ' ASC')
            );
            $storages = $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            return [[], ['Could not inspect storages for storage index naming audit: ' . $e->getMessage()]];
        }

        foreach ($storages as $storage) {
            $storageId = (int) ($storage['id'] ?? 0);
            $storageName = strtolower(trim((string) ($storage['name'] ?? '')));

            if ($storageId <= 0 || $storageName === '' || !preg_match('/^[a-z0-9_]+$/', $storageName)) {
                continue;
            }

            $expectedTableName = $prefix . $storageName;
            $physicalTable = $tableNames[strtolower($expectedTableName)] ?? '';
            if ($physicalTable === '') {
                continue;
            }

            try {
                $indexes = AuditTableSupportHelper::getTableIndexes($db, $physicalTable);
            } catch (\Throwable $e) {
                $errors[] = 'Could not inspect indexes on ' . $physicalTable . ' for storage index naming audit: ' . $e->getMessage();
                continue;
            }

            $legacyIndexes = [];
            $usedNames = array_fill_keys(array_map('strtolower', array_keys($indexes)), true);
            foreach ($indexes as $indexName => $definition) {
                $columns = (array) ($definition['columns'] ?? []);
                $columnName = strtolower((string) ($columns[0]['name'] ?? ''));

                if (
                    count($columns) !== 1
                    || !in_array($columnName, $legacyColumns, true)
                    || strcasecmp((string) $indexName, $columnName) !== 0
                ) {
                    continue;
                }

                $suggestedName = self::buildIndexName($columnName, $usedNames);
                $legacyIndexes[] = [
                    'name' => (string) $indexName,
                    'column' => $columnName,
                    'suggested_name' => $suggestedName,
                ];
                unset($usedNames[strtolower((string) $indexName)]);
                $usedNames[strtolower($suggestedName)] = true;
            }

            if ($legacyIndexes === []) {
                continue;
            }

            $issues[] = [
                'storage_id' => $storageId,
                'storage_name' => trim((string) (($storage['title'] ?? '') !== '' ? $storage['title'] : ($storage['name'] ?? ''))),
                'table' => AuditTableSupportHelper::toAlias($physicalTable, $prefix),
                'indexes' => $legacyIndexes,
            ];
        }

        return [$issues, $errors];
    }

    /**
     * @param array<string,bool> $usedNames
     */
    private static function buildIndexName(string $column, array $usedNames): string
    {
        $base = 'idx_' . strtolower($column);
        if (strlen($base) > 64) {
            $base = substr($base, 0, 55) . '_' . substr(sha1($column), 0, 8);
        }
        $name = $base;
        $suffix = 2;

        while (isset($usedNames[strtolower($name)])) {
            $suffixText = '_' . $suffix++;
            $name = substr($base, 0, 64 - strlen($suffixText)) . $suffixText;
        }

        return $name;
    }
}
