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
$allChecks = array_values(array_filter(
    (array) ($audit['checks'] ?? []),
    static fn($check): bool => is_array($check)
));
$formId = (int) (($audit['form']['id'] ?? 0));

// reference_id findings get their own section: they are usually many at once and
// share a single root cause, so mixing them into the general list buries the
// other checks.
$referenceChecks = array_values(array_filter(
    $allChecks,
    static fn(array $check): bool => (string) ($check['code'] ?? '') === 'element_reference'
));
$otherChecks = array_values(array_filter(
    $allChecks,
    static fn(array $check): bool => (string) ($check['code'] ?? '') !== 'element_reference'
));

$statusBadges = [
    FormAuditService::STATUS_OK => ['bg-success', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_OK'],
    FormAuditService::STATUS_WARNING => ['text-bg-warning', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_WARNING'],
    FormAuditService::STATUS_ERROR => ['bg-danger', 'COM_CONTENTBUILDERNG_AUDIT_STATUS_ERROR'],
];

// Every repair this panel can offer, keyed by the audit check code. Keeping the
// whole set in one map is what stops the panel from silently drifting out of
// sync with the checks the audit service produces.
$checkRepairs = [
    'theme_empty' => [
        'task' => 'form.repairThemePlugin',
        'tip' => 'COM_CONTENTBUILDERNG_AUDIT_THEME_REPAIR_TIP',
        'label' => 'COM_CONTENTBUILDERNG_AUDIT_THEME_REPAIR',
        'theme' => 'thoth',
    ],
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
    'editable_field_without_item' => [
        'task' => 'form.repairEditableFieldItem',
        'tip' => 'COM_CONTENTBUILDERNG_AUDIT_EDITABLE_ITEM_REPAIR_TIP',
        'label' => 'COM_CONTENTBUILDERNG_AUDIT_EDITABLE_ITEM_REPAIR',
        'requires_field' => true,
    ],
];
$checkReferenceTooltips = [
    'CBNG-AUDIT-FIELD-MISSING-IN-EDIT' => 'COM_CONTENTBUILDERNG_AUDIT_REFERENCE_FIELD_MISSING_IN_EDIT_TIP',
];

$renderCheck = static function (array $check) use ($statusBadges, $checkRepairs, $checkReferenceTooltips, $formId): string {
    [$badgeClass, $badgeKey] = $statusBadges[(string) ($check['status'] ?? FormAuditService::STATUS_WARNING)]
        ?? $statusBadges[FormAuditService::STATUS_WARNING];
    $checkReference = trim((string) ($check['reference'] ?? ''));
    $checkField = trim((string) ($check['field'] ?? ''));
    $repair = $checkRepairs[(string) ($check['code'] ?? '')] ?? null;

    if ($repair !== null && (!empty($repair['requires_field']) && $checkField === '')) {
        $repair = null;
    }

    $html = '<li class="list-group-item d-flex align-items-start gap-2">'
        . '<span class="badge ' . $badgeClass . '">' . Text::_($badgeKey) . '</span>'
        . '<div>'
        . htmlspecialchars((string) ($check['message'] ?? ''), ENT_QUOTES, 'UTF-8');

    if ($checkReference !== '') {
        $tipKey = $checkReferenceTooltips[$checkReference] ?? '';
        $html .= '<small class="d-block text-muted"'
            . ($tipKey !== '' ? ' title="' . htmlspecialchars(Text::_($tipKey), ENT_QUOTES, 'UTF-8') . '"' : '')
            . '>'
            . htmlspecialchars(Text::sprintf('COM_CONTENTBUILDERNG_AUDIT_REFERENCE', $checkReference), ENT_QUOTES, 'UTF-8')
            . '</small>';
    }

    if ($repair !== null && $formId > 0) {
        $label = Text::_($repair['label']);
        $html .= '<button type="button" class="btn btn-sm btn-warning ms-2"'
            . ' data-cb-form-audit-repair-button'
            . ' data-cb-form-audit-task="' . htmlspecialchars($repair['task'], ENT_QUOTES, 'UTF-8') . '"'
            . ' data-cb-form-audit-id="' . $formId . '"';

        if ($checkField !== '') {
            $html .= ' data-cb-form-audit-field="' . htmlspecialchars($checkField, ENT_QUOTES, 'UTF-8') . '"';
        }

        if (!empty($repair['theme'])) {
            $html .= ' data-cb-form-audit-theme="' . htmlspecialchars((string) $repair['theme'], ENT_QUOTES, 'UTF-8') . '"';
        }

        $html .= ' title="' . htmlspecialchars(Text::_($repair['tip']), ENT_QUOTES, 'UTF-8') . '"'
            . ' aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">'
            . '<span class="fa-solid fa-wrench me-1" aria-hidden="true"></span>'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . '</button>';
    }

    return $html . '</div></li>';
};
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

    <h2 class="h5 mt-4"><?php echo Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_ELEMENT_REFERENCE_CONSISTENCY'); ?></h2>
    <ul class="list-group">
        <?php if ($referenceChecks === []) : ?>
            <li class="list-group-item d-flex align-items-start gap-2">
                <span class="badge bg-success"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_STATUS_OK'); ?></span>
                <span><?php echo Text::_('COM_CONTENTBUILDERNG_ABOUT_AUDIT_ELEMENT_REFERENCE_CONSISTENCY_OK'); ?></span>
            </li>
        <?php else : ?>
            <?php foreach ($referenceChecks as $check) : ?>
                <?php echo $renderCheck($check); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <h2 class="h5 mt-4"><?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_CHECKS_HEADING'); ?></h2>
    <ul class="list-group">
        <?php if ($otherChecks === []) : ?>
            <li class="list-group-item">
                <?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_INFO_UNAVAILABLE'); ?>
            </li>
        <?php else : ?>
            <?php foreach ($otherChecks as $check) : ?>
                <?php echo $renderCheck($check); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
