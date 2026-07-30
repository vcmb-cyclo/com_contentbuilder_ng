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

namespace CB\Component\Contentbuilderng\Administrator\Helper;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

/**
 * Explains why a view's source form could not be resolved.
 *
 * FormSourceFactory::getForm() only answers "resolved or not", which surfaced
 * everywhere as the generic "view not found" message even when the view row
 * itself was perfectly fine and it was the referenced storage (or its physical
 * table) that had gone missing. This helper re-runs the same lookups the source
 * type classes do, and reports the first condition that actually failed.
 */
final class FormSourceDiagnosticHelper
{
    /**
     * Returns a translated, specific reason why the source cannot be resolved.
     *
     * @param string     $type        the view's source type
     * @param int|string $referenceId the view's reference_id (storage id / BF form id)
     */
    public static function describe(string $type, $referenceId): string
    {
        $type = trim($type);
        $referenceId = (int) $referenceId;

        if ($type === '') {
            return Text::_('COM_CONTENTBUILDERNG_SOURCE_DIAG_TYPE_MISSING');
        }

        if ($referenceId <= 0) {
            return Text::_('COM_CONTENTBUILDERNG_SOURCE_DIAG_REFERENCE_MISSING');
        }

        $normalizedType = match ($type) {
            'com_contentbuilder' => 'com_contentbuilderng',
            'com_breezingforms',
            'com_breezingforms_ng',
            'com_breezingformsng' => 'com_breezingformsng',
            default => $type,
        };

        return match ($normalizedType) {
            'com_contentbuilderng' => self::describeStorageSource($referenceId),
            'com_breezingformsng' => self::describeBreezingFormsSource($referenceId),
            default => Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_TYPE_UNKNOWN', $type),
        };
    }

    private static function describeStorageSource(int $storageId): string
    {
        try {
            $db = RuntimeContextHelper::getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'name', 'title', 'published', 'bytable']))
                ->from($db->quoteName('#__contentbuilderng_storages'))
                ->where($db->quoteName('id') . ' = :storageId')
                ->bind(':storageId', $storageId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);
            $storage = $db->loadAssoc();
        } catch (\Throwable $e) {
            return Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_LOOKUP_FAILED', $e->getMessage());
        }

        if (!is_array($storage)) {
            return Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_STORAGE_MISSING', $storageId);
        }

        $storageLabel = trim((string) ($storage['title'] ?? '')) !== ''
            ? trim((string) $storage['title'])
            : trim((string) ($storage['name'] ?? ''));

        if ((int) ($storage['published'] ?? 0) !== 1) {
            return Text::sprintf(
                'COM_CONTENTBUILDERNG_SOURCE_DIAG_STORAGE_UNPUBLISHED',
                $storageLabel,
                $storageId
            );
        }

        $storageName = trim((string) ($storage['name'] ?? ''));
        if ($storageName === '') {
            return Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_STORAGE_NO_TABLE', $storageLabel, $storageId);
        }

        // Mirrors the source type class: an external table is used verbatim,
        // an internal one is prefixed.
        $physicalTable = (int) ($storage['bytable'] ?? 0) > 0
            ? $storageName
            : $db->getPrefix() . $storageName;

        try {
            $tables = array_map('strtolower', (array) $db->getTableList());
        } catch (\Throwable $e) {
            return Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_LOOKUP_FAILED', $e->getMessage());
        }

        if (!in_array(strtolower($physicalTable), $tables, true)) {
            return Text::sprintf(
                'COM_CONTENTBUILDERNG_SOURCE_DIAG_STORAGE_TABLE_MISSING',
                $storageLabel,
                $storageId,
                $physicalTable
            );
        }

        return Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_STORAGE_UNRESOLVED', $storageLabel, $storageId);
    }

    private static function describeBreezingFormsSource(int $formId): string
    {
        try {
            $db = RuntimeContextHelper::getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'name', 'title', 'published']))
                ->from($db->quoteName('#__facileforms_forms'))
                ->where($db->quoteName('id') . ' = :formId')
                ->bind(':formId', $formId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);
            $form = $db->loadAssoc();
        } catch (\Throwable $e) {
            return Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_BF_LOOKUP_FAILED', $e->getMessage());
        }

        if (!is_array($form)) {
            return Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_BF_MISSING', $formId);
        }

        $formLabel = trim((string) ($form['title'] ?? '')) !== ''
            ? trim((string) $form['title'])
            : trim((string) ($form['name'] ?? ''));

        if ((int) ($form['published'] ?? 0) !== 1) {
            return Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_BF_UNPUBLISHED', $formLabel, $formId);
        }

        return Text::sprintf('COM_CONTENTBUILDERNG_SOURCE_DIAG_BF_UNRESOLVED', $formLabel, $formId);
    }
}
