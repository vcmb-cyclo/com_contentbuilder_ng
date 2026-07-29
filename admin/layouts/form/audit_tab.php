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
$checks = array_values(array_filter(
    (array) ($audit['checks'] ?? []),
    static fn($check): bool => is_array($check)
));
$statusBadges = [
    FormAuditService::STATUS_OK => ['bg-success', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_OK'],
    FormAuditService::STATUS_WARNING => ['text-bg-warning', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_WARNING'],
    FormAuditService::STATUS_ERROR => ['bg-danger', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_ERROR'],
];
?>
<div class="p-3">
    <div class="alert alert-info">
        <?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_SAVED_CONFIGURATION_NOTICE'); ?>
    </div>

    <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_CHECKS_HEADING'); ?></h2>
    <ul class="list-group">
        <?php if ($checks === []) : ?>
            <li class="list-group-item">
                <?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_INFO_UNAVAILABLE'); ?>
            </li>
        <?php else : ?>
            <?php foreach ($checks as $check) : ?>
                <?php [$badgeClass, $badgeKey] = $statusBadges[(string) ($check['status'] ?? FormAuditService::STATUS_WARNING)] ?? $statusBadges[FormAuditService::STATUS_WARNING]; ?>
                <li class="list-group-item d-flex align-items-start gap-2">
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo Text::_($badgeKey); ?></span>
                    <span><?php echo htmlspecialchars((string) ($check['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
