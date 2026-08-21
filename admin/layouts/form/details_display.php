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
$editablePrepareSnippetOptions = is_array($displayData['editablePrepareSnippetOptions'] ?? null) ? $displayData['editablePrepareSnippetOptions'] : [];
$prepareEffectOptions = is_array($displayData['prepareEffectOptions'] ?? null) ? $displayData['prepareEffectOptions'] : [];
$templateAuditChecks = (array) ($displayData['templateAuditChecks'] ?? []);
$templateAuditReferences = (array) ($displayData['templateAuditReferences'] ?? []);


$prepareExamplesText = <<<'TXT'
// Ici, vous pouvez modifier les libellés et les valeurs de chaque élément avant le rendu du template d'édition.

// Adaptez la valeur et le libellé avec du code PHP.
// Les données sont stockées dans le tableau $items.

// Exemple : la valeur du champ "NAME" sera affichée en majuscules, en gras et en rouge.
$items["NAME"]["value"] = strtoupper((string) $items["NAME"]["value"]);
$items["NAME"]["value"] = "<b>" . $items["NAME"]["value"] . "</b>";
$items["NAME"]["value"] = "<span style=\"color:#dc3545\">" . $items["NAME"]["value"] . "</span>";

// Exemple : la valeur du champ "COUNT" sera affichée en rouge si elle est < 0.
$items["COUNT"]["value"] = (is_numeric((string) $items["COUNT"]["value"]) && (float) $items["COUNT"]["value"] < 0)
    ? "<span style=\"color:#dc3545\">" . $items["COUNT"]["value"] . "</span>"
    : $items["COUNT"]["value"];

// Exemple : ajouter la date courante à un champ de libellé.
$items["DATE_LABEL"]["label"] = (string) $items["DATE_LABEL"]["label"] . " (" . date("Y-m-d") . ")";
TXT;
?>
<?php echo LayoutHelper::render('form.template_audit_errors', [
    'auditChecks' => $templateAuditChecks,
    'references' => $templateAuditReferences,
]); ?>
<h3 id="cb-form-details-display" class="mb-3">
    <?php echo Text::_('COM_CONTENTBUILDERNG_TAB_DETAILS_DISPLAY'); ?>
</h3>
<p class="text-muted mb-3">
    <?php echo Text::_('COM_CONTENTBUILDERNG_TAB_DETAILS_DISPLAY_INTRO'); ?>
</p>
<div class="row gx-3 gy-1 mt-0 align-items-stretch mb-3" id="cb-form-details-show-buttons-row">
    <div class="col-12 d-flex" id="cb-form-details-show-buttons">
        <div class="border rounded bg-body p-3 d-flex flex-column flex-grow-1" id="cb-form-details-show-buttons-card">
            <h4 class="h6 text-body-secondary mb-2">
                <?php echo Text::_('COM_CONTENTBUILDERNG_SHOW_BUTTON_OPTIONS'); ?>
            </h4>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div>
                    <input type="hidden" name="jform[cb_show_details_top_bar]" id="cb-form-details-show-top-bar-hidden" value="0" />
                    <?php echo $renderCheckbox('jform[cb_show_details_top_bar]', 'cb_show_details_top_bar', (bool) ($item->cb_show_details_top_bar ?? true)); ?>
                    <label class="form-check-label" for="cb_show_details_top_bar">
                        <span class="editlinktip hasTip" title="<?php echo Text::_('COM_CONTENTBUILDERNG_DETAIL_TOP_BAR_DESC'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDERNG_DETAIL_TOP_BAR'); ?>
                        </span>
                    </label>
                </div>
                <div>
                    <input type="hidden" name="jform[cb_show_details_bottom_bar]" id="cb-form-details-show-bottom-bar-hidden" value="0" />
                    <?php echo $renderCheckbox('jform[cb_show_details_bottom_bar]', 'cb_show_details_bottom_bar', (bool) ($item->cb_show_details_bottom_bar ?? false)); ?>
                    <label class="form-check-label" for="cb_show_details_bottom_bar">
                        <span class="editlinktip hasTip" title="<?php echo Text::_('COM_CONTENTBUILDERNG_DETAIL_BOTTOM_BAR_DESC'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDERNG_DETAIL_BOTTOM_BAR'); ?>
                        </span>
                    </label>
                </div>
                <?php if (is_callable($renderCheckbox)) : ?>
                    <div class="form-check mb-0 flex-shrink-0 text-nowrap border-start ps-3">
                        <input type="hidden" name="jform[details_template_locked]" value="0" />
                        <?php echo $renderCheckbox('jform[details_template_locked]', 'details_template_locked', !empty($item->details_template_locked)); ?>
                        <label class="form-check-label" for="details_template_locked">
                            <span class="editlinktip hasTip" title="<?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_LOCKED_TIP'); ?>">
                                <span class="fa-solid fa-lock me-1" aria-hidden="true"></span><?php echo Text::_('COM_CONTENTBUILDERNG_TEMPLATE_LOCKED'); ?>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="jform[create_sample]" id="cb_create_sample_flag" value="0" />
</div>
<div id="cb-form-details-template-field-group">
    <?php echo $form ? $form->renderField('details_template') : ''; ?>
</div>
<hr />
<h3 id="cb-form-details-prepare" class="mb-3">
    <?php echo Text::_('COM_CONTENTBUILDERNG_DETAILS_PREPARE_MODE_TITLE'); ?>
</h3>
<?php
echo LayoutHelper::render(
    'form.prepare_editor',
    [
        'snippetOptions' => $editablePrepareSnippetOptions,
        'effectOptions' => $prepareEffectOptions,
        'selectId' => 'cb_details_prepare_snippet_select',
        'slotName' => 'cb_details_prepare_slot',
        'slotValueId' => 'cb_details_prepare_slot_value',
        'slotLabelId' => 'cb_details_prepare_slot_label',
        'effectSelectId' => 'cb_details_prepare_effect_select',
        'addButtonId' => 'cb_add_details_prepare_snippet',
        'addButtonOnclick' => 'cbInsertDetailsPrepareSnippet();',
        'hintId' => 'cb_details_prepare_snippet_hint',
        'fieldName' => 'jform[details_prepare]',
        'editorId' => 'jform_details_prepare',
        'value' => (string) ($item->details_prepare ?? ''),
        'emptyValue' => Text::_('COM_CONTENTBUILDERNG_DETAILS_PREPARE_EMPTY_VALUE') . "\n",
        'addButtonTextKey' => 'COM_CONTENTBUILDERNG_DETAILS_PREPARE_SNIPPET_ADD',
        'showExamplesModal' => true,
        'examplesText' => $prepareExamplesText,
    ],
    JPATH_COMPONENT_ADMINISTRATOR . '/layouts'
);
?>
