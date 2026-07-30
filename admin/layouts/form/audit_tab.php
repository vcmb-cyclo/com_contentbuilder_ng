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
$templateRepairs = [
    'editable_template_empty' => [
        'task' => 'form.repairEditableTemplate',
        'tip' => 'COM_CONTENTBUILDERNG_AUDIT_EDITABLE_TEMPLATE_REPAIR_TIP',
        'label' => 'COM_CONTENTBUILDERNG_AUDIT_EDITABLE_TEMPLATE_REPAIR',
    ],
    'details_template_empty' => [
        'task' => 'form.repairDetailsTemplate',
        'tip' => 'COM_CONTENTBUILDERNG_AUDIT_DETAILS_TEMPLATE_REPAIR_TIP',
        'label' => 'COM_CONTENTBUILDERNG_AUDIT_DETAILS_TEMPLATE_REPAIR',
    ],
    'templates_empty' => [
        'task' => 'form.repairTemplates',
        'tip' => 'COM_CONTENTBUILDERNG_AUDIT_TEMPLATES_REPAIR_TIP',
        'label' => 'COM_CONTENTBUILDERNG_AUDIT_EDITABLE_TEMPLATE_REPAIR',
    ],
];
?>
<div class="p-3" data-cb-form-audit-panel>
    <div class="alert alert-info">
        <span class="fa-solid fa-bug me-1" aria-hidden="true"></span><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_SAVED_CONFIGURATION_NOTICE'); ?>
    </div>

    <?php if (!empty($audit['info'])) : ?>
        <h2 class="h5"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_INFO_HEADING'); ?></h2>
        <table class="table table-sm">
            <tbody>
                <?php foreach ($audit['info'] as $label => $value) : ?>
                    <tr>
                        <th scope="row" class="w-25"><?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></th>
                        <td><?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2 class="h5 mt-4"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_CHECKS_HEADING'); ?></h2>
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
                        <?php $templateRepair = $templateRepairs[(string) ($check['code'] ?? '')] ?? null; ?>
                        <?php if ($templateRepair !== null && $formId > 0) : ?>
                            <button
                                type="button"
                                class="btn btn-sm btn-warning ms-2"
                                data-cb-form-audit-repair-button
                                data-cb-form-audit-task="<?php echo htmlspecialchars($templateRepair['task'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-cb-form-audit-id="<?php echo $formId; ?>"
                                title="<?php echo htmlspecialchars(Text::_($templateRepair['tip']), ENT_QUOTES, 'UTF-8'); ?>"
                                aria-label="<?php echo htmlspecialchars(Text::_($templateRepair['label']), ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <span class="fa-solid fa-wrench me-1" aria-hidden="true"></span><?php echo Text::_($templateRepair['label']); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
