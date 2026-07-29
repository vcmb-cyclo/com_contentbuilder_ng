<?php

/**
 * @package     ContentBuilderNG
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Service\FormAuditService;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$audit = (array) ($this->audit ?? ['info' => [], 'checks' => []]);
$auditForm = (array) ($audit['form'] ?? []);
$statusBadges = [
    FormAuditService::STATUS_OK => ['bg-success', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_OK'],
    FormAuditService::STATUS_WARNING => ['text-bg-warning', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_WARNING'],
    FormAuditService::STATUS_ERROR => ['bg-danger', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_ERROR'],
];
$auditTitle = trim((string) ($auditForm['name'] ?? '')) !== ''
    ? Text::sprintf('COM_CONTENTBUILDERNG_AUDIT_MODAL_TITLE', trim((string) $auditForm['name']), (int) ($auditForm['id'] ?? 0))
    : Text::_('COM_CONTENTBUILDERNG_AUDIT');
$formId = (int) ($auditForm['id'] ?? 0);
$referenceChecks = array_values(array_filter(
    (array) ($audit['checks'] ?? []),
    static fn($check): bool => is_array($check) && (string) ($check['code'] ?? '') === 'element_reference'
));
$otherChecks = array_values(array_filter(
    (array) ($audit['checks'] ?? []),
    static fn($check): bool => is_array($check) && (string) ($check['code'] ?? '') !== 'element_reference'
));
?>
<div class="p-3" data-cb-form-audit-panel>
    <h1 class="h4 mb-3"><?php echo htmlspecialchars($auditTitle, ENT_QUOTES, 'UTF-8'); ?></h1>

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

    <h2 class="h5 mt-4">24. <?php echo Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_ELEMENT_REFERENCE_CONSISTENCY'); ?></h2>
    <ul class="list-group">
        <?php if ($referenceChecks === []) : ?>
            <li class="list-group-item d-flex align-items-start gap-2">
                <span class="badge bg-success"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_STATUS_OK'); ?></span>
                <span><?php echo Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_ELEMENT_REFERENCE_CONSISTENCY_OK'); ?></span>
            </li>
        <?php else : ?>
            <?php foreach ($referenceChecks as $check) : ?>
                <?php [$badgeClass, $badgeKey] = $statusBadges[(string) ($check['status'] ?? FormAuditService::STATUS_WARNING)] ?? $statusBadges[FormAuditService::STATUS_WARNING]; ?>
                <li class="list-group-item d-flex align-items-start gap-2">
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo Text::_($badgeKey); ?></span>
                    <span><?php echo htmlspecialchars((string) ($check['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <?php if ($otherChecks !== []) : ?>
        <h2 class="h5 mt-4"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_CHECKS_HEADING'); ?></h2>
        <ul class="list-group">
        <?php foreach ($otherChecks as $check) : ?>
            <?php [$badgeClass, $badgeKey] = $statusBadges[(string) ($check['status'] ?? FormAuditService::STATUS_WARNING)] ?? $statusBadges[FormAuditService::STATUS_WARNING]; ?>
            <li class="list-group-item d-flex align-items-start gap-2">
                <span class="badge <?php echo $badgeClass; ?>"><?php echo Text::_($badgeKey); ?></span>
                <div>
                    <?php echo htmlspecialchars((string) ($check['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    <?php if ((string) ($check['code'] ?? '') === 'theme_empty' && $formId > 0) : ?>
                        <form
                            class="d-inline ms-2"
                            action="index.php?option=com_contentbuilderng"
                            method="post"
                            data-cb-form-audit-repair
                        >
                            <input type="hidden" name="option" value="com_contentbuilderng">
                            <input type="hidden" name="task" value="form.repairThemePlugin">
                            <input type="hidden" name="id" value="<?php echo $formId; ?>">
                            <?php echo HTMLHelper::_('form.token'); ?>
                            <button
                                type="submit"
                                class="btn btn-sm btn-warning"
                                name="theme_plugin"
                                value="thoth"
                                title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_AUDIT_THEME_REPAIR_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_AUDIT_THEME_REPAIR'), ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <span class="fa-solid fa-wrench me-1" aria-hidden="true"></span><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_THEME_REPAIR'); ?>
                            </button>
                        </form>
                    <?php elseif ((string) ($check['code'] ?? '') === 'editable_template_empty' && $formId > 0) : ?>
                        <form
                            class="d-inline ms-2"
                            action="index.php?option=com_contentbuilderng"
                            method="post"
                            data-cb-form-audit-repair
                        >
                            <input type="hidden" name="option" value="com_contentbuilderng">
                            <input type="hidden" name="task" value="form.repairEditableTemplate">
                            <input type="hidden" name="id" value="<?php echo $formId; ?>">
                            <?php echo HTMLHelper::_('form.token'); ?>
                            <button
                                type="submit"
                                class="btn btn-sm btn-warning"
                                value="thoth"
                                title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_AUDIT_EDITABLE_TEMPLATE_REPAIR_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_AUDIT_EDITABLE_TEMPLATE_REPAIR'), ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <span class="fa-solid fa-wrench me-1" aria-hidden="true"></span><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_EDITABLE_TEMPLATE_REPAIR'); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
