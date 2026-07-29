<?php

/**
 * @package     ContentBuilderNG
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var array<string,mixed> $displayData */
$audit = is_array($displayData['audit'] ?? null) ? $displayData['audit'] : [];
$data = is_array($audit['data'] ?? null) ? $audit['data'] : [];
$source = trim((string) ($data['source_type'] ?? '')) . ' / ' . (int) ($data['source_reference_id'] ?? 0);
$sourceTitle = trim((string) ($data['source_title'] ?? ''));
?>
<div class="p-3">
    <div class="alert alert-info">
        <?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_SAVED_CONFIGURATION_NOTICE'); ?>
    </div>

    <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_TAB_DATA'); ?></h2>
    <table class="table table-sm">
        <tbody>
            <tr>
                <th scope="row" class="w-50"><?php echo Text::_('COM_CONTENTBUILDERNG_FORM_SOURCE'); ?></th>
                <td>
                    <?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>
                    <?php if ($sourceTitle !== '') : ?>
                        <br><span class="text-muted"><?php echo htmlspecialchars($sourceTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_INFO_RECORDS'); ?></th>
                <td>
                    <?php echo htmlspecialchars((string) ($data['records_total'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>
                    <?php if (empty($data['records_count_available'])) : ?>
                        <span class="text-muted">(<?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_INFO_UNAVAILABLE'); ?>)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_INFO_ELEMENTS'); ?></th>
                <td>
                    <?php echo (int) ($data['elements_total'] ?? 0); ?>
                    (<?php echo (int) ($data['elements_published'] ?? 0); ?> <?php echo Text::_('COM_CONTENTBUILDERNG_LIST_STATES_PUBLISHED'); ?>,
                    <?php echo (int) ($data['elements_editable'] ?? 0); ?> <?php echo Text::_('COM_CONTENTBUILDERNG_EDITABLE'); ?>)
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_INFO_PUBLISHED'); ?></th>
                <td><?php echo !empty($data['published']) ? Text::_('JYES') : Text::_('JNO'); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_INFO_DEBUG'); ?></th>
                <td><?php echo !empty($data['debug_mode']) ? Text::_('JYES') : Text::_('JNO'); ?></td>
            </tr>
        </tbody>
    </table>
</div>
