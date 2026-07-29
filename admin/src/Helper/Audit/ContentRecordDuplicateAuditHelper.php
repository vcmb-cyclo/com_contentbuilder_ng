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

/**
 * Detects and repairs duplicate rows in #__contentbuilderng_records.
 *
 * The (type, reference_id, record_id) triplet is the application-level key
 * used everywhere records are looked up (see EditModel::applyRecordKeyConditions()).
 * A race between two concurrent submissions of the same record can insert two
 * identical rows for that key; every read that joins on this table (notably
 * the BreezingForms storage source, which GROUP_CONCAT's subrecord values per
 * joined row) then duplicates the joined data once per extra row.
 */
final class ContentRecordDuplicateAuditHelper
{
    /**
     * @return array{0:array<int,array{type:string,reference_id:int,record_id:int,count:int,keep_id:int,duplicate_ids:array<int,int>}>,1:array<int,string>}
     */
    public static function inspect(DatabaseInterface $db): array
    {
        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'type', 'reference_id', 'record_id']))
                ->from($db->quoteName('#__contentbuilderng_records'))
                ->order($db->quoteName('id') . ' ASC');
            $db->setQuery($query);
            $rows = $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            return [[], ['Could not inspect #__contentbuilderng_records for duplicate rows: ' . $e->getMessage()]];
        }

        return [self::buildGroups($rows), []];
    }

    /**
     * @param array<int,array{id:int|string,type:string,reference_id:int|string,record_id:int|string}> $rows
     * @return array<int,array{type:string,reference_id:int,record_id:int,count:int,keep_id:int,duplicate_ids:array<int,int>}>
     */
    private static function buildGroups(array $rows): array
    {
        $bucket = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $type = trim((string) ($row['type'] ?? ''));
            $referenceId = (int) ($row['reference_id'] ?? 0);
            $recordId = (int) ($row['record_id'] ?? 0);
            $key = $type . '/' . $referenceId . '/' . $recordId;

            $bucket[$key][] = $id;
        }

        $groups = [];
        foreach ($bucket as $key => $ids) {
            if (count($ids) < 2) {
                continue;
            }

            [$type, $referenceId, $recordId] = explode('/', (string) $key, 3);
            sort($ids);
            $keepId = array_shift($ids);

            $groups[] = [
                'type' => $type,
                'reference_id' => (int) $referenceId,
                'record_id' => (int) $recordId,
                'count' => count($ids) + 1,
                'keep_id' => $keepId,
                'duplicate_ids' => array_values($ids),
            ];
        }

        usort(
            $groups,
            static fn(array $a, array $b): int => [$a['type'], $a['reference_id'], $a['record_id']]
                <=> [$b['type'], $b['reference_id'], $b['record_id']]
        );

        return $groups;
    }

    /**
     * @return array{
     *   scanned:int,
     *   groups:int,
     *   rows_removed:int,
     *   unchanged:int,
     *   errors:int,
     *   items:array<int,array{
     *     type:string,
     *     reference_id:int,
     *     record_id:int,
     *     keep_id:int,
     *     removed_ids:array<int,int>,
     *     status:string,
     *     error:string
     *   }>,
     *   warnings:array<int,string>
     * }
     */
    public static function repair(DatabaseInterface $db): array
    {
        [$groups, $inspectErrors] = self::inspect($db);

        $scanned = 0;
        try {
            $db->setQuery(
                $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__contentbuilderng_records'))
            );
            $scanned = (int) $db->loadResult();
        } catch (\Throwable $e) {
            $inspectErrors[] = 'Could not count #__contentbuilderng_records rows: ' . $e->getMessage();
        }

        $summary = [
            'scanned' => $scanned,
            'groups' => count($groups),
            'rows_removed' => 0,
            'unchanged' => 0,
            'errors' => 0,
            'items' => [],
            'warnings' => $inspectErrors,
        ];

        foreach ($groups as $group) {
            $type = (string) ($group['type'] ?? '');
            $referenceId = (int) ($group['reference_id'] ?? 0);
            $recordId = (int) ($group['record_id'] ?? 0);
            $keepId = (int) ($group['keep_id'] ?? 0);
            $duplicateIds = array_values(array_filter(
                array_map('intval', (array) ($group['duplicate_ids'] ?? [])),
                static fn(int $id): bool => $id > 0
            ));

            if ($keepId <= 0 || $duplicateIds === []) {
                $summary['unchanged']++;
                $summary['items'][] = [
                    'type' => $type,
                    'reference_id' => $referenceId,
                    'record_id' => $recordId,
                    'keep_id' => $keepId,
                    'removed_ids' => [],
                    'status' => 'unchanged',
                    'error' => '',
                ];
                continue;
            }

            try {
                $db->setQuery(
                    $db->getQuery(true)
                        ->delete($db->quoteName('#__contentbuilderng_records'))
                        ->where($db->quoteName('id') . ' IN (' . implode(',', $duplicateIds) . ')')
                );
                $db->execute();

                $removed = (int) $db->getAffectedRows();
                $summary['rows_removed'] += $removed;
                $summary['items'][] = [
                    'type' => $type,
                    'reference_id' => $referenceId,
                    'record_id' => $recordId,
                    'keep_id' => $keepId,
                    'removed_ids' => $duplicateIds,
                    'status' => 'repaired',
                    'error' => '',
                ];
            } catch (\Throwable $e) {
                $summary['errors']++;
                $summary['items'][] = [
                    'type' => $type,
                    'reference_id' => $referenceId,
                    'record_id' => $recordId,
                    'keep_id' => $keepId,
                    'removed_ids' => $duplicateIds,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $summary;
    }
}
