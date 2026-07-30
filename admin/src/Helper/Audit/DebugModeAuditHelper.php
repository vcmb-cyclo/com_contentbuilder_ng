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
use Joomla\Database\ParameterType;

/**
 * Reports views left with frontend debug mode enabled.
 *
 * Debug mode exposes the debug panel (permissions, filters, field mapping,
 * request logs) to frontend visitors of that view, so it is meant for
 * troubleshooting only and should not stay on in production. Repairing simply
 * turns the flag off; the per-view debug sub-options are left untouched so the
 * previous setup is restored as-is when debug mode is switched back on.
 */
final class DebugModeAuditHelper
{
    /**
     * @return array{0:array<int,array{form_id:int,form_name:string,form_title:string,published:int,options:array<int,string>}>,1:array<int,string>}
     */
    public static function inspect(DatabaseInterface $db): array
    {
        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName([
                    'id',
                    'name',
                    'title',
                    'published',
                    'debug_show_bf_id',
                    'debug_show_cb_id',
                    'debug_enable_logs',
                    'debug_show_request_logs',
                    'debug_show_permissions',
                    'debug_show_filters',
                ]))
                ->from($db->quoteName('#__contentbuilderng_forms'))
                ->where($db->quoteName('debug_mode') . ' = 1')
                ->order($db->quoteName('id') . ' ASC');
            $db->setQuery($query);
            $rows = $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            return [[], ['Could not inspect views for debug mode: ' . $e->getMessage()]];
        }

        $optionColumns = [
            'debug_show_bf_id' => 'COM_CONTENTBUILDERNG_DEBUG_SHOW_BF_ID',
            'debug_show_cb_id' => 'COM_CONTENTBUILDERNG_DEBUG_SHOW_CB_ID',
            'debug_enable_logs' => 'COM_CONTENTBUILDERNG_DEBUG_ENABLE_LOGS',
            'debug_show_request_logs' => 'COM_CONTENTBUILDERNG_DEBUG_SHOW_REQUEST_LOGS',
            'debug_show_permissions' => 'COM_CONTENTBUILDERNG_DEBUG_SHOW_PERMISSIONS',
            'debug_show_filters' => 'COM_CONTENTBUILDERNG_DEBUG_SHOW_FILTERS',
        ];

        $issues = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $formId = (int) ($row['id'] ?? 0);
            if ($formId <= 0) {
                continue;
            }

            $options = [];
            foreach ($optionColumns as $column => $languageKey) {
                if ((int) ($row[$column] ?? 0) === 1) {
                    $options[] = $languageKey;
                }
            }

            $issues[] = [
                'form_id' => $formId,
                'form_name' => trim((string) ($row['name'] ?? '')),
                'form_title' => trim((string) ($row['title'] ?? '')),
                'published' => (int) ($row['published'] ?? 0),
                'options' => $options,
            ];
        }

        return [$issues, []];
    }

    /**
     * Turns debug mode off, either for the given views or for all of them.
     *
     * @param array<int,int|string> $selectedFormIds empty means "every view still in debug mode"
     *
     * @return array{
     *   scanned:int,
     *   issues:int,
     *   repaired:int,
     *   unchanged:int,
     *   errors:int,
     *   items:array<int,array{form_id:int,form_name:string,status:string,error:string}>,
     *   warnings:array<int,string>
     * }
     */
    public static function repair(DatabaseInterface $db, array $selectedFormIds = []): array
    {
        [$issues, $warnings] = self::inspect($db);

        $selectedFormIds = array_values(array_unique(array_filter(
            array_map('intval', $selectedFormIds),
            static fn(int $formId): bool => $formId > 0
        )));

        if ($selectedFormIds !== []) {
            $issues = array_values(array_filter(
                $issues,
                static fn(array $issue): bool => in_array((int) $issue['form_id'], $selectedFormIds, true)
            ));
        }

        $summary = [
            'scanned' => count($issues),
            'issues' => count($issues),
            'repaired' => 0,
            'unchanged' => 0,
            'errors' => 0,
            'items' => [],
            'warnings' => $warnings,
        ];

        foreach ($issues as $issue) {
            $formId = (int) $issue['form_id'];
            $formName = (string) ($issue['form_name'] ?? '');

            try {
                $update = $db->getQuery(true)
                    ->update($db->quoteName('#__contentbuilderng_forms'))
                    ->set($db->quoteName('debug_mode') . ' = 0')
                    ->where($db->quoteName('id') . ' = :formId')
                    ->bind(':formId', $formId, ParameterType::INTEGER);
                $db->setQuery($update);
                $db->execute();

                if ((int) $db->getAffectedRows() > 0) {
                    $summary['repaired']++;
                    $status = 'repaired';
                } else {
                    $summary['unchanged']++;
                    $status = 'unchanged';
                }

                $summary['items'][] = [
                    'form_id' => $formId,
                    'form_name' => $formName,
                    'status' => $status,
                    'error' => '',
                ];
            } catch (\Throwable $e) {
                $summary['errors']++;
                $summary['items'][] = [
                    'form_id' => $formId,
                    'form_name' => $formName,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $summary;
    }
}
