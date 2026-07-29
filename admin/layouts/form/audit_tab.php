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
$formId = (int) (($audit['form']['id'] ?? 0));
$statusBadges = [
    FormAuditService::STATUS_OK => ['bg-success', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_OK'],
    FormAuditService::STATUS_WARNING => ['text-bg-warning', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_WARNING'],
    FormAuditService::STATUS_ERROR => ['bg-danger', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_ERROR'],
];
?>
<div class="p-3" data-cb-form-audit-panel>
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
                    <div>
                        <?php echo htmlspecialchars((string) ($check['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ((string) ($check['code'] ?? '') === 'editable_template_empty' && $formId > 0) : ?>
                            <button
                                type="button"
                                class="btn btn-sm btn-warning ms-2"
                                data-cb-form-audit-repair-button
                                data-cb-form-audit-task="form.repairEditableTemplate"
                                data-cb-form-audit-id="<?php echo $formId; ?>"
                                title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_AUDIT_EDITABLE_TEMPLATE_REPAIR_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_AUDIT_EDITABLE_TEMPLATE_REPAIR'), ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <span class="fa-solid fa-wrench me-1" aria-hidden="true"></span><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_EDITABLE_TEMPLATE_REPAIR'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
