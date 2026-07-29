<?php

/**
 * @package     ContentBuilderNG
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Service\FormAuditService;
use Joomla\CMS\Language\Text;

/** @var array<string,mixed> $displayData */
$audit = is_array($displayData['audit'] ?? null) ? $displayData['audit'] : [];
$performance = is_array($audit['performance'] ?? null) ? $audit['performance'] : [];
$checks = array_values(array_filter(
    (array) ($audit['checks'] ?? []),
    static fn($check): bool => is_array($check) && (string) ($check['code'] ?? '') === 'performance'
));
?>
<div class="p-3">
    <div class="alert alert-info">
        <span class="fa-solid fa-bug me-1" aria-hidden="true"></span><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_SAVED_CONFIGURATION_NOTICE'); ?>
    </div>

    <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_TAB_PERFORMANCE'); ?></h2>
    <?php if ($performance === []) : ?>
        <div class="alert alert-secondary">
            <?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_INFO_UNAVAILABLE'); ?>
        </div>
    <?php else : ?>
        <table class="table table-sm">
            <tbody>
                <?php foreach ($performance as $label => $value) : ?>
                    <tr>
                        <th scope="row" class="w-50"><?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></th>
                        <td><?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2 class="h5 mt-4"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_CHECKS_HEADING'); ?></h2>
    <ul class="list-group">
        <?php if ($checks === []) : ?>
            <li class="list-group-item d-flex align-items-start gap-2">
                <span class="badge bg-success"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_STATUS_OK'); ?></span>
                <span><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_CHECK_ALL_OK'); ?></span>
            </li>
        <?php else : ?>
            <?php foreach ($checks as $check) : ?>
                <?php $status = (string) ($check['status'] ?? FormAuditService::STATUS_WARNING); ?>
                <?php $badgeClass = $status === FormAuditService::STATUS_ERROR ? 'bg-danger' : 'text-bg-warning'; ?>
                <?php $badgeKey = $status === FormAuditService::STATUS_ERROR ? 'COM_CONTENTBUILDERNG_AUDIT_STATUS_ERROR' : 'COM_CONTENTBUILDERNG_AUDIT_STATUS_WARNING'; ?>
                <li class="list-group-item d-flex align-items-start gap-2">
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo Text::_($badgeKey); ?></span>
                    <span><?php echo htmlspecialchars((string) ($check['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
