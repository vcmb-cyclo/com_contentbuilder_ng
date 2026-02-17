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

final class PackedDataMigrationHelper
{
    private static function isMigrationCandidate(string $raw): bool
    {
        $decoded = base64_decode($raw, true);

        if ($decoded === false) {
            return false;
        }

        // Already in modern format.
        if (strpos($decoded, 'j:') === 0) {
            return false;
        }

        return true;
    }

    /**
     * Migrate legacy packed payloads in database columns to the JSON-based packed format.
     *
     * @return array{
     *   scanned:int,
     *   candidates:int,
     *   migrated:int,
     *   unchanged:int,
     *   errors:int,
     *   tables:array<int,array{table:string,column:string,scanned:int,candidates:int,migrated:int,unchanged:int,errors:int}>
     * }
     */
    public static function migrate(): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $targets = [
            [
                'table' => '#__contentbuilder_ng_elements',
                'primaryKey' => 'id',
                'column' => 'options',
            ],
            [
                'table' => '#__contentbuilder_ng_forms',
                'primaryKey' => 'id',
                'column' => 'config',
            ],
        ];

        $summary = [
            'scanned' => 0,
            'candidates' => 0,
            'migrated' => 0,
            'unchanged' => 0,
            'errors' => 0,
            'tables' => [],
        ];

        foreach ($targets as $target) {
            $tableStats = [
                'table' => (string) $target['table'],
                'column' => (string) $target['column'],
                'scanned' => 0,
                'candidates' => 0,
                'migrated' => 0,
                'unchanged' => 0,
                'errors' => 0,
            ];

            try {
                $query = $db->getQuery(true)
                    ->select([
                        $db->quoteName($target['primaryKey']),
                        $db->quoteName($target['column']),
                    ])
                    ->from($db->quoteName($target['table']))
                    ->where($db->quoteName($target['column']) . ' IS NOT NULL')
                    ->where($db->quoteName($target['column']) . " <> ''");

                $db->setQuery($query);
                $rows = $db->loadAssocList();
            } catch (\Throwable $e) {
                $tableStats['errors']++;
                $summary['errors']++;
                $summary['tables'][] = $tableStats;
                continue;
            }

            if (!is_array($rows)) {
                $summary['tables'][] = $tableStats;
                continue;
            }

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $id = (int) ($row[$target['primaryKey']] ?? 0);
                $raw = (string) ($row[$target['column']] ?? '');

                $tableStats['scanned']++;
                $summary['scanned']++;

                if ($id <= 0 || $raw === '') {
                    $tableStats['unchanged']++;
                    $summary['unchanged']++;
                    continue;
                }

                if (!self::isMigrationCandidate($raw)) {
                    $tableStats['unchanged']++;
                    $summary['unchanged']++;
                    continue;
                }

                $tableStats['candidates']++;
                $summary['candidates']++;

                $sentinel = new \stdClass();
                $decoded = ContentbuilderLegacyHelper::decodePackedData($raw, $sentinel, false);

                if ($decoded === $sentinel) {
                    $tableStats['errors']++;
                    $summary['errors']++;
                    continue;
                }

                $encoded = ContentbuilderLegacyHelper::encodePackedData($decoded);

                if ($encoded === $raw) {
                    $tableStats['unchanged']++;
                    $summary['unchanged']++;
                    continue;
                }

                try {
                    $update = $db->getQuery(true)
                        ->update($db->quoteName($target['table']))
                        ->set($db->quoteName($target['column']) . ' = ' . $db->quote($encoded))
                        ->where($db->quoteName($target['primaryKey']) . ' = ' . $id);

                    $db->setQuery($update);
                    $db->execute();
                } catch (\Throwable $e) {
                    $tableStats['errors']++;
                    $summary['errors']++;
                    continue;
                }

                $tableStats['migrated']++;
                $summary['migrated']++;
            }

            $summary['tables'][] = $tableStats;
        }

        return $summary;
    }
}
