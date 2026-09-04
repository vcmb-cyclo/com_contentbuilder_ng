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
 * (StorageModel::syncStorageDataTableOrBytable()). They are represented by
 * regular metadata rows, initially unpublished, so an administrator can
 * expose them without altering the physical table.
 */
final class StorageSystemFieldHelper
{
    /**
     * @return array<string,array{label:string,sql_type:string,required:bool}>
     */
    public static function definitions(): array
    {
        return [
            'id' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_ID'),
                'sql_type' => 'int',
                'required' => true,
            ],
            'user_id' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_USER_ID'),
                'sql_type' => 'int',
                'required' => true,
            ],
            'created' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_CREATED'),
                'sql_type' => 'datetime',
                'required' => true,
            ],
            'created_by' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_CREATED_BY'),
                'sql_type' => 'varchar',
                'required' => true,
            ],
            'modified_user_id' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_MODIFIED_USER_ID'),
                'sql_type' => 'int',
                'required' => true,
            ],
            'modified' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_MODIFIED'),
                'sql_type' => 'datetime',
                'required' => false,
            ],
            'modified_by' => [
                'label' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_MODIFIED_BY'),
                'sql_type' => 'varchar',
                'required' => true,
            ],
        ];
    }

    public static function isSystemFieldName(string $name): bool
    {
        return isset(self::definitions()[$name]);
    }
}
