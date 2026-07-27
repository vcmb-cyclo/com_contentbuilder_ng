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

/**
 * Reserved columns automatically added to every Storage data table
 * (StorageModel::syncStorageDataTableOrBytable()) but never exposed as a
 * manageable #__contentbuilderng_storage_fields row by default. Mirrors the
 * BreezingForms "Champ système" feature (Form screen): lets an admin
 * explicitly surface one of these existing columns as a regular field
 * (visible, sortable, searchable) without altering the table.
 */
final class StorageSystemFieldHelper
{
    /**
     * @return array<string,array{label:string,sql_type:string}>
     */
    public static function definitions(): array
    {
        return [
            'id' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_ID'),
                'sql_type' => 'int',
            ],
            'user_id' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_USER_ID'),
                'sql_type' => 'int',
            ],
            'created' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_CREATED'),
                'sql_type' => 'datetime',
            ],
            'created_by' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_CREATED_BY'),
                'sql_type' => 'varchar',
            ],
            'modified_user_id' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_MODIFIED_USER_ID'),
                'sql_type' => 'int',
            ],
            'modified' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_MODIFIED'),
                'sql_type' => 'datetime',
            ],
            'modified_by' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_MODIFIED_BY'),
                'sql_type' => 'varchar',
            ],
        ];
    }

    public static function isSystemFieldName(string $name): bool
    {
        return isset(self::definitions()[$name]);
    }
}
