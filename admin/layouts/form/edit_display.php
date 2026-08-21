<?php

/**
 * @package     ContentBuilderNG
 * @author      Markus Bopp
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$item = $displayData['item'] ?? null;
$form = $displayData['form'] ?? null;
$renderCheckbox = $displayData['renderCheckbox'] ?? null;
$canEditByType = !empty($displayData['canEditByType']);
$isBreezingFormsType = !empty($displayData['isBreezingFormsType']);
$breezingFormsProvidedMessage = (string) ($displayData['breezingFormsProvidedMessage'] ?? '');
$breezingFormsEditableToken = (string) ($displayData['breezingFormsEditableToken'] ?? '');
$editablePrepareSnippetOptions = is_array($displayData['editablePrepareSnippetOptions'] ?? null) ? $displayData['editablePrepareSnippetOptions'] : [];
$prepareEffectOptions = is_array($displayData['prepareEffectOptions'] ?? null) ? $displayData['prepareEffectOptions'] : [];
$templateAuditChecks = (array) ($displayData['templateAuditChecks'] ?? []);
$templateAuditReferences = (array) ($displayData['templateAuditReferences'] ?? []);
?>
<?php echo LayoutHelper::render('form.template_audit_errors', [
    'auditChecks' => $templateAuditChecks,
    'references' => $templateAuditReferences,
]); ?>
<h3 id="cb-form-edit-display" class="mb-1">
    <?php echo Text::_('COM_CONTENTBUILDERNG_TAB_EDIT_DISPLAY'); ?>
</h3>
<p class="text-muted mb-2">
    <?php echo Text::_('COM_CONTENTBUILDERNG_TAB_EDIT_DISPLAY_INTRO'); ?>
</p>
<input type="hidden" name="jform[edit_by_type]" id="cb-form-edit-by-type-hidden" value="0" />
<div class="row gx-2 gy-1 mt-0 align-items-stretch mb-2" id="cb-form-edit-show-buttons-row">
    <div class="col-12 d-flex" id="cb-form-edit-show-buttons">
        <div class="border rounded bg-body p-2 d-flex flex-column flex-grow-1" id="cb-form-edit-show-buttons-card">
            <h4 class="h6 text-body-secondary mb-1">
                <?php echo Text::_('COM_CONTENTBUILDERNG_SHOW_BUTTON_OPTIONS'); ?>
            </h4>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div>
                    <input type="hidden" name="jform[cb_show_top_bar]" id="cb-form-edit-show-top-bar-hidden" value="0" />
                    <?php echo $renderCheckbox('jform[cb_show_top_bar]', 'cb_show_top_bar', (bool) ($item->cb_show_top_bar ?? true)); ?>
                    <label class="form-check-label" for="cb_show_top_bar">
                        <span class="editlinktip hasTip" title="<?php echo Text::_('COM_CONTENTBUILDERNG_EDIT_TOP_BAR_DESC'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDERNG_EDIT_TOP_BAR'); ?>
                        </span>
                    </label>
                </div>
                <div>
                    <input type="hidden" name="jform[cb_show_bottom_bar]" id="cb-form-edit-show-bottom-bar-hidden" value="0" />
                    <?php echo $renderCheckbox('jform[cb_show_bottom_bar]', 'cb_show_bottom_bar', (bool) ($item->cb_show_bottom_bar ?? false)); ?>
                    <label class="form-check-label" for="cb_show_bottom_bar">
                        <span class="editlinktip hasTip" title="<?php echo Text::_('COM_CONTENTBUILDERNG_EDIT_BOTTOM_BAR_DESC'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDERNG_EDIT_BOTTOM_BAR'); ?>
                        </span>
                    </label>
                </div>
                <?php if (is_callable($renderCheckbox) && (empty($item->edit_by_type) || !$isBreezingFormsType)) : ?>
                    <div class="form-check mb-0 flex-shrink-0 text-nowrap border-start ps-3">
                        <input type="hidden" name="jform[editable_template_locked]" value="0" />
                        <?php echo $renderCheckbox('jform[editable_template_locked]', 'editable_template_locked', !empty($item->editable_template_locked)); ?>
                        <label class="form-check-label" for="editable_template_locked">
                            <span class="editlinktip hasTip" title="<?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_LOCKED_TIP'); ?>">
                                <span class="fa-solid fa-lock me-1" aria-hidden="true"></span><?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_LOCKED'); ?>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>
                <?php if ($canEditByType) : ?>
                    <div class="form-check mb-0 flex-shrink-0 border-start ps-3" id="cb-form-edit-by-type-field-group">
                        <?php echo is_callable($renderCheckbox) ? $renderCheckbox('jform[edit_by_type]', 'edit_by_type', (bool) ($item->edit_by_type ?? false)) : ''; ?>
                        <label class="form-check-label" for="edit_by_type">
                            <span class="editlinktip hasTip" title="<?php echo Text::_('COM_CONTENTBUILDERNG_TYPE_EDIT_TIP'); ?>">
                                <?php echo Text::_('COM_CONTENTBUILDERNG_TYPE_EDIT'); ?>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (empty($item->edit_by_type) || !$isBreezingFormsType) : ?>
        <input type="hidden" name="jform[create_editable_sample]" id="cb_create_editable_sample_flag" value="0" />
    <?php endif; ?>
</div>
<?php if (!empty($item->edit_by_type) && $isBreezingFormsType) : ?>
    <?php echo $breezingFormsProvidedMessage; ?>
    <input type="hidden" name="jform[editable_template]" id="cb-form-edit-editable-template-hidden" value="<?php echo htmlspecialchars($breezingFormsEditableToken, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="jform[upload_directory]" id="cb-form-edit-upload-directory-hidden" value="<?php echo htmlspecialchars(trim((string) ($item->upload_directory ?? '')) ?: JPATH_SITE . '/media/com_contentbuilderng/upload', ENT_QUOTES, 'UTF-8'); ?>" />
<?php else : ?>
    <input type="hidden" name="jform[protect_upload_directory]" id="cb-form-edit-protect-upload-directory-hidden" value="0" />
    <div id="cb-form-edit-upload" class="cb-upload-box mb-2">
        <div class="d-flex flex-wrap align-items-center gap-2" id="cb-form-edit-upload-row">
            <div class="d-flex align-items-center gap-2 flex-grow-1" id="cb-form-edit-upload-directory-field-group">
                <label for="upload_directory" class="form-label mb-0 text-nowrap"><span class="editlinktip hasTip"
                        title="<?php echo Text::_('COM_CONTENTBUILDERNG_UPLOAD_DIRECTORY_TIP'); ?>">
                        <?php echo Text::_('COM_CONTENTBUILDERNG_ELEMENT_OPTIONS_UPLOAD_DIRECTORY'); ?>
                    </span></label>
                <input class="form-control form-control-sm flex-grow-1" type="text"
                    value="<?php echo htmlspecialchars(trim((string) ($item->upload_directory ?? '')) ?: JPATH_SITE . '/media/com_contentbuilderng/upload', ENT_QUOTES, 'UTF-8'); ?>"
                    name="jform[upload_directory]" id="upload_directory" />
            </div>
            <div class="flex-shrink-0" id="cb-form-edit-protect-upload-directory-field-group">
                <div class="form-check mb-0">
                    <?php echo is_callable($renderCheckbox) ? $renderCheckbox('jform[protect_upload_directory]', 'protect_upload_directory', trim((string) ($item->protect_upload_directory ?? '')) !== '') : ''; ?>
                    <label class="form-check-label" for="protect_upload_directory">
                        <?php echo Text::_('COM_CONTENTBUILDERNG_PROTECT_UPLOAD_DIRECTORY'); ?>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div id="cb-form-edit-template-field-group">
        <?php echo $form ? $form->renderField('editable_template') : ''; ?>
    </div>
<?php endif; ?>
<hr />
<h3 id="cb-form-edit-prepare" class="mb-3">
    <?php echo Text::_('COM_CONTENTBUILDERNG_DETAILS_PREPARE_MODE_TITLE'); ?>
</h3>
<?php if (!empty($item->edit_by_type)) : ?>
    <?php echo $breezingFormsProvidedMessage; ?>
    <input type="hidden" name="jform[editable_prepare]" id="cb-form-edit-editable-prepare-hidden" value="<?php echo htmlspecialchars($item->editable_prepare ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
<?php else : ?>
    <?php
    echo LayoutHelper::render(
        'form.prepare_editor',
        [
            'snippetOptions' => $editablePrepareSnippetOptions,
            'effectOptions' => $prepareEffectOptions,
            'selectId' => 'cb_editable_prepare_snippet_select',
            'slotName' => 'cb_editable_prepare_slot',
            'slotValueId' => 'cb_editable_prepare_slot_value',
            'slotLabelId' => 'cb_editable_prepare_slot_label',
            'effectSelectId' => 'cb_editable_prepare_effect_select',
            'addButtonId' => 'cb_add_editable_prepare_snippet',
            'addButtonOnclick' => 'cbInsertEditablePrepareSnippet();',
            'hintId' => 'cb_editable_prepare_snippet_hint',
            'fieldName' => 'jform[editable_prepare]',
            'editorId' => 'jform_editable_prepare',
            'value' => (string) ($item->editable_prepare ?? ''),
            'emptyValue' => Text::_('COM_CONTENTBUILDERNG_EDITABLE_PREPARE_EMPTY_VALUE') . "\n",
            'addButtonTextKey' => 'COM_CONTENTBUILDERNG_EDITABLE_PREPARE_SNIPPET_ADD',
            'showExamplesModal' => false,
        ],
        JPATH_COMPONENT_ADMINISTRATOR . '/layouts'
    );
    ?>
<?php endif; ?>
