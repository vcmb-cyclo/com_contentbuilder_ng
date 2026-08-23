<?php

/**
 * @package     ContentBuilderNG
 * @author      Markus Bopp
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

final class BfContentRecordOrphanAuditHelper
{
    /**
     * @return array{0:array<int,array{form_id:int,count:int,records:array<int,array{cb_record_id:int,record_id:int}>}>,1:array<int,string>}
     */
    public static function inspect(DatabaseInterface $db): array
    {
        try {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('records.id', 'cb_record_id'),
                    $db->quoteName('records.reference_id', 'form_id'),
                    $db->quoteName('records.record_id'),
                ])
                ->from($db->quoteName('#__contentbuilderng_records', 'records'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__facileforms_records', 'bf_records')
                    . ' ON ' . $db->quoteName('bf_records.id') . ' = ' . $db->quoteName('records.record_id')
                    . ' AND ' . $db->quoteName('bf_records.form') . ' = ' . $db->quoteName('records.reference_id')
                )
                ->where($db->quoteName('records.type') . ' = ' . $db->quote('com_breezingformsng'))
                ->where($db->quoteName('bf_records.id') . ' IS NULL')
                ->order($db->quoteName('records.reference_id') . ' ASC')
                ->order($db->quoteName('records.record_id') . ' ASC')
                ->order($db->quoteName('records.id') . ' ASC');
            $db->setQuery($query);
            $rows = $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            return [[], ['Could not inspect orphan BreezingForms ContentBuilder records: ' . $e->getMessage()]];
        }

        $forms = [];
        foreach ($rows as $row) {
            $formId = (int) ($row['form_id'] ?? 0);
            $cbRecordId = (int) ($row['cb_record_id'] ?? 0);
            $recordId = (int) ($row['record_id'] ?? 0);

            if ($formId <= 0 || $cbRecordId <= 0 || $recordId <= 0) {
                continue;
            }

            if (!isset($forms[$formId])) {
                $forms[$formId] = [
                    'form_id' => $formId,
                    'count' => 0,
                    'records' => [],
                ];
            }

            $forms[$formId]['count']++;
            $forms[$formId]['records'][] = [
                'cb_record_id' => $cbRecordId,
                'record_id' => $recordId,
            ];
        }

        ksort($forms, SORT_NUMERIC);

        return [array_values($forms), []];
    }

    /**
     * @return array{scanned:int,forms:int,rows_removed:int,unchanged:int,errors:int,items:array<int,array<string,mixed>>,warnings:array<int,string>}
     */
    public static function repair(DatabaseInterface $db): array
    {
        [$forms, $warnings] = self::inspect($db);
        $scanned = (int) array_sum(array_column($forms, 'count'));
        $summary = [
            'scanned' => $scanned,
            'forms' => count($forms),
            'rows_removed' => 0,
            'unchanged' => 0,
            'errors' => 0,
            'items' => [],
            'warnings' => $warnings,
        ];

        if ($forms === [] || $warnings !== []) {
            return $summary;
        }

        try {
            $db->transactionStart();

            foreach ($forms as $form) {
                $formId = (int) ($form['form_id'] ?? 0);
                $removedForForm = 0;
                $unchangedForForm = 0;

                foreach ((array) ($form['records'] ?? []) as $record) {
                    $cbRecordId = (int) ($record['cb_record_id'] ?? 0);
                    $recordId = (int) ($record['record_id'] ?? 0);
                    $sourceExists = $db->getQuery(true)
                        ->select('1')
                        ->from($db->quoteName('#__facileforms_records', 'bf_records'))
                        ->where($db->quoteName('bf_records.id') . ' = ' . $recordId)
                        ->where($db->quoteName('bf_records.form') . ' = ' . $formId);
                    $delete = $db->getQuery(true)
                        ->delete($db->quoteName('#__contentbuilderng_records'))
                        ->where($db->quoteName('id') . ' = ' . $cbRecordId)
                        ->where($db->quoteName('type') . ' = ' . $db->quote('com_breezingformsng'))
                        ->where($db->quoteName('reference_id') . ' = ' . $formId)
                        ->where($db->quoteName('record_id') . ' = ' . $recordId)
                        ->where('NOT EXISTS (' . (string) $sourceExists . ')');
                    $db->setQuery($delete);
                    $db->execute();

                    if ((int) $db->getAffectedRows() === 1) {
                        $removedForForm++;
                    } else {
                        $unchangedForForm++;
                    }
                }

                $summary['rows_removed'] += $removedForForm;
                $summary['unchanged'] += $unchangedForForm;
                $summary['items'][] = [
                    'form_id' => $formId,
                    'candidates' => (int) ($form['count'] ?? 0),
                    'rows_removed' => $removedForForm,
                    'unchanged' => $unchangedForForm,
                    'status' => $removedForForm > 0 ? 'repaired' : 'unchanged',
                    'error' => '',
                ];
            }

            $db->transactionCommit();
        } catch (\Throwable $e) {
            try {
                $db->transactionRollback();
            } catch (\Throwable) {
            }

            $summary['rows_removed'] = 0;
            $summary['unchanged'] = 0;
            $summary['errors'] = 1;
            $summary['items'] = [[
                'form_id' => 0,
                'candidates' => $scanned,
                'rows_removed' => 0,
                'unchanged' => 0,
                'status' => 'error',
                'error' => $e->getMessage(),
            ]];
        }

        return $summary;
    }
}
