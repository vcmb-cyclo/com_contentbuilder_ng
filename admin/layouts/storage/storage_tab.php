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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use CB\Component\Contentbuilderng\Administrator\Helper\ContentbuilderngHelper;
use CB\Component\Contentbuilderng\Administrator\Helper\StorageColumnTypeHelper;
use CB\Component\Contentbuilderng\Administrator\Helper\StorageSystemFieldHelper;

$item = $displayData['item'] ?? null;
$storageId = (int) ($displayData['storageId'] ?? 0);
$addFieldTooltip = (string) ($displayData['addFieldTooltip'] ?? '');
$sortLink = $displayData['sortLink'] ?? null;
$fields = is_iterable($displayData['fields'] ?? null) ? $displayData['fields'] : [];
$fieldsCount = (int) ($displayData['fieldsCount'] ?? 0);
$recordsCount = $displayData['recordsCount'] ?? null;
$pagination = $displayData['pagination'] ?? null;
$ordering = !empty($displayData['ordering']);
$storageSqlTypeOptions = StorageColumnTypeHelper::options();
// Le type SQL ne peut être modifié qu'avant toute donnée (ALTER TABLE sûr
// tant que la table est vide) ; jamais pour un champ système (son type
// physique est fixe, cf. StorageSystemFieldHelper).
$canEditSqlType = !$item->bytable && $recordsCount !== null && (int) $recordsCount === 0;

$storageFieldColumns = [
    'id' => Text::_('COM_CONTENTBUILDERNG_ID'),
    'name' => Text::_('COM_CONTENTBUILDERNG_NAME'),
    'title' => Text::_('COM_CONTENTBUILDERNG_LIST_STATES_TITLE'),
    'sql_type' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE'),
    'field_size' => Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_SIZE'),
    'required' => Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_REQUIRED'),
    'group' => Text::_('COM_CONTENTBUILDERNG_STORAGE_GROUP'),
    'order' => Text::_('COM_CONTENTBUILDERNG_ORDERBY'),
    'publish' => Text::_('COM_CONTENTBUILDERNG_LIST_STATES_PUBLISHED'),
];
?>
<?php if (!$item->bytable && $item->id) : ?>
    <script type="application/json" id="cb-storage-field-sql-types"><?php echo json_encode($storageSqlTypeOptions, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
    <script type="application/json" id="cb-storage-field-sql-size-limits"><?php echo json_encode([
        'varchar' => StorageColumnTypeHelper::maxSize('varchar'),
        'text' => StorageColumnTypeHelper::maxSize('text'),
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
<?php endif; ?>

<div class="card border rounded-3 mb-3 cb-storage-fields-editor">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h2 class="h5 mb-0"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE'); ?></h2>
            <small class="text-muted"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_TAB_TOOLTIP'); ?></small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <?php if ($storageId > 0) : ?>
                <label class="form-check d-flex align-items-center gap-2 mb-0"
                    title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_HIDE_UNPUBLISHED_SYSTEM_FIELDS'), ENT_QUOTES, 'UTF-8'); ?>">
                    <input
                        class="form-check-input mt-0"
                        type="checkbox"
                        id="cb-storage-hide-unpublished-system-fields"
                        data-cb-storage-hide-unpublished-system-fields="1"
                        aria-controls="cb-storage-fields-tbody"
                        checked>
                    <span class="form-check-label">
                        <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_HIDE_UNPUBLISHED_SYSTEM_FIELDS'); ?>
                    </span>
                </label>
            <?php endif; ?>
            <?php if (!$item->bytable && !$item->id) : ?>
                <div class="alert alert-info py-1 px-2 mb-0 d-inline-block">
                    <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_SAVE_FIRST_ADD_FIELDS'); ?>
                </div>
            <?php endif; ?>
            <div class="dropdown cb-storage-columns-dropdown">
                    <button type="button"
                        class="btn btn-primary btn-sm dropdown-toggle"
                        id="cb-storage-columns-toggle"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-haspopup="true"
                        aria-expanded="false"
                        title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_COLUMNS_TOGGLE_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="cb-storage-columns-count"><?php echo count($storageFieldColumns); ?>/<?php echo count($storageFieldColumns); ?> <?php echo Text::_('COM_CONTENTBUILDERNG_COLUMNS'); ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-2 cb-storage-columns-menu" aria-labelledby="cb-storage-columns-toggle">
                        <?php foreach ($storageFieldColumns as $columnKey => $columnLabel) : ?>
                            <label class="dropdown-item form-check d-flex align-items-center gap-2 mb-0">
                                <input class="form-check-input mt-0 cb-storage-column-toggle"
                                    type="checkbox"
                                    value="<?php echo htmlspecialchars($columnKey, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-cb-storage-column-toggle="1"
                                    checked>
                                <span><?php echo htmlspecialchars($columnLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                            </label>
                        <?php endforeach; ?>
                        <div class="dropdown-divider my-2"></div>
                        <button type="button" class="btn btn-link btn-sm px-2 cb-storage-columns-reset" data-cb-storage-columns-reset="1" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_COLUMNS_RESET_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDERNG_RESET'); ?>
                        </button>
                    </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0 cb-storage-fields-table" data-cb-storage-fields-ordering="<?php echo $ordering ? '1' : '0'; ?>">
                <thead>
                    <tr>
                        <th width="20" data-cb-storage-col="check">
                            <input class="form-check-input" type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);" aria-label="<?php echo htmlspecialchars(Text::_('JGLOBAL_CHECK_ALL'), ENT_QUOTES, 'UTF-8'); ?>">
                        </th>
                        <th width="60" class="text-nowrap" data-cb-storage-col="id">
                            <?php echo is_callable($sortLink) ? $sortLink(Text::_('COM_CONTENTBUILDERNG_ID'), 'id') : Text::_('COM_CONTENTBUILDERNG_ID'); ?>
                        </th>
                        <th data-cb-storage-col="name" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_NAME_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo is_callable($sortLink) ? $sortLink(Text::_('COM_CONTENTBUILDERNG_NAME'), 'name') : Text::_('COM_CONTENTBUILDERNG_NAME'); ?>
                        </th>
                        <th data-cb-storage-col="title" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_TITLE_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo is_callable($sortLink) ? $sortLink(Text::_('COM_CONTENTBUILDERNG_LIST_STATES_TITLE'), 'title') : Text::_('COM_CONTENTBUILDERNG_LIST_STATES_TITLE'); ?>
                        </th>
                        <th data-cb-storage-col="sql_type" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo is_callable($sortLink) ? $sortLink(Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE'), 'sql_type') : Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE'); ?>
                        </th>
                        <th width="90" data-cb-storage-col="field_size" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_SIZE_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo is_callable($sortLink) ? $sortLink(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_SIZE'), 'field_size') : Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_SIZE'); ?>
                        </th>
                        <th width="50" class="text-center" data-cb-storage-col="required" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_REQUIRED_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo is_callable($sortLink) ? $sortLink(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_REQUIRED'), 'required') : Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_REQUIRED'); ?>
                        </th>
                        <th data-cb-storage-col="group" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_GROUP_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo is_callable($sortLink) ? $sortLink(Text::_('COM_CONTENTBUILDERNG_STORAGE_GROUP'), 'group_definition') : Text::_('COM_CONTENTBUILDERNG_STORAGE_GROUP'); ?>
                        </th>
                        <th class="cb-order-col" data-cb-storage-col="order" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_ORDER_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo is_callable($sortLink) ? $sortLink(Text::_('COM_CONTENTBUILDERNG_ORDERBY'), 'ordering') : Text::_('COM_CONTENTBUILDERNG_ORDERBY'); ?>
                        </th>
                        <th data-cb-storage-col="publish" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_PUBLISHED_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo is_callable($sortLink) ? $sortLink(Text::_('COM_CONTENTBUILDERNG_LIST_STATES_PUBLISHED'), 'published') : Text::_('COM_CONTENTBUILDERNG_LIST_STATES_PUBLISHED'); ?>
                        </th>
                        <th width="90" class="text-center" data-cb-storage-col="actions">
                            <?php if (!$item->bytable && $item->id) : ?>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button"
                                        id="cb-storage-field-add-button"
                                        class="btn btn-success group-add cb-storage-field-add hasTooltip"
                                        title="<?php echo htmlspecialchars($addFieldTooltip, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_ADD_FIELD_BUTTON'), ENT_QUOTES, 'UTF-8'); ?>">
                                        <span class="icon-plus" aria-hidden="true"></span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </th>
                    </tr>
                </thead>
                <tbody id="cb-storage-fields-tbody">
                <?php $n = $fieldsCount; ?>
                <?php foreach ($fields as $i => $row) :
                    $id = (int) ($row->id ?? 0);
                    $rawName = (string) ($row->name ?? '');
                    $name = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
                    $rawTitle = (string) ($row->title ?? '');
                    $title = htmlspecialchars($rawTitle, ENT_QUOTES, 'UTF-8');
                    $sqlType = StorageColumnTypeHelper::normalize((string) ($row->sql_type ?? StorageColumnTypeHelper::DEFAULT_TYPE));
                    $sqlTypeLabel = htmlspecialchars(StorageColumnTypeHelper::label($sqlType), ENT_QUOTES, 'UTF-8');
                    $fieldSize = StorageColumnTypeHelper::normalizeSize($sqlType, $row->field_size ?? null);
                    $groupDefinition = htmlspecialchars((string) ($row->group_definition ?? ''), ENT_QUOTES, 'UTF-8');
                    $isGroup = !empty($row->is_group);
                    $isSystemField = StorageSystemFieldHelper::isSystemFieldName($rawName);
                    $rowFieldEditable = !$isSystemField && (int) ($item->bytable ?? 0) === 0;
                    $checked = $isSystemField
                        ? '<input class="form-check-input" type="checkbox" id="cb' . (int) $i . '" value="' . $id . '" disabled>'
                        : '<input class="form-check-input" type="checkbox" id="cb' . (int) $i . '" name="cid[]" value="' . $id . '" onclick="Joomla.isChecked(this.checked);">';
                    $published = ContentbuilderngHelper::listPublish('storage', $row, $i);
                    $rowSqlTypeEditable = $canEditSqlType && !$isSystemField;
                    $rowSizeEditable = $rowSqlTypeEditable
                        || (!$isSystemField && (int) ($item->bytable ?? 0) === 0 && $sqlType === 'varchar');
                    $required = !empty($row->required);
                    // Contrairement au type SQL, basculer le caractère obligatoire
                    // reste sûr même sur une table déjà peuplée (les valeurs NULL
                    // existantes sont comblées avant de poser la contrainte, cf.
                    // StorageColumnTypeHelper::enforceRequired()) : aucune
                    // restriction "table vide" n'est donc nécessaire ici, à la
                    // différence de $rowSqlTypeEditable.
                    $rowRequiredEditable = !$isSystemField && (int) ($item->bytable ?? 0) === 0;
                    $requiredIconClass = $required ? 'fa-solid fa-asterisk text-danger' : 'fa-regular fa-circle text-muted';
                    $requiredTipText = Text::_(
                        $required
                            ? 'COM_CONTENTBUILDERNG_STORAGE_FIELD_REQUIRED_YES_TIP'
                            : 'COM_CONTENTBUILDERNG_STORAGE_FIELD_REQUIRED_NO_TIP'
                    );
                    ?>
                    <tr class="row<?php echo $i % 2; ?><?php echo $isSystemField && empty($row->published) ? ' cb-storage-system-field-unpublished d-none' : ''; ?>"
                        data-cb-row-id="<?php echo $id; ?>"
                        <?php echo $isSystemField ? 'data-cb-system-field="1"' : ''; ?>
                        data-cb-item-label="<?php echo $title !== '' ? $title : $name; ?>">
                        <td class="text-center" data-cb-storage-col="check"><?php echo $checked; ?></td>
                        <td class="text-nowrap" data-cb-storage-col="id"><?php echo $id; ?></td>
                        <td data-cb-storage-col="name">
                            <?php echo $name; ?>
                            <?php if ($isSystemField) : ?>
                                <span class="fa-solid fa-gear ms-1" aria-hidden="true" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_MANAGED_TIP'), ENT_QUOTES, 'UTF-8'); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td data-cb-storage-col="title">
                            <?php if ($isSystemField) : ?>
                                <?php echo $title; ?>
                            <?php else : ?>
                                <input
                                    type="text"
                                    class="form-control form-control-sm cb-storage-field-title-input<?php echo htmlspecialchars($rowFieldEditable ? ' cb-storage-field-editable' : '', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-field-id="<?php echo htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-previous-value="<?php echo htmlspecialchars($rawTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                    title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_TITLE_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                    value="<?php echo htmlspecialchars($rawTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php echo htmlspecialchars($rowFieldEditable ? 'disabled' : '', ENT_QUOTES, 'UTF-8'); ?>
                                >
                            <?php endif; ?>
                        </td>
                        <td data-cb-storage-col="sql_type">
                            <?php if ($rowSqlTypeEditable) : ?>
                                <select class="form-select form-select-sm cb-storage-field-type-select cb-storage-field-editable" data-field-id="<?php echo $id; ?>" data-previous-value="<?php echo htmlspecialchars($sqlType, ENT_QUOTES, 'UTF-8'); ?>" style="width:auto; max-width:12rem;" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_TIP'), ENT_QUOTES, 'UTF-8'); ?>" disabled>
                                    <?php foreach ($storageSqlTypeOptions as $sqlTypeValue => $sqlTypeOptionLabel) : ?>
                                        <option
                                            value="<?php echo htmlspecialchars($sqlTypeValue, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-supports-size="<?php echo StorageColumnTypeHelper::supportsSize($sqlTypeValue) ? '1' : '0'; ?>"
                                            data-default-size="<?php echo (int) StorageColumnTypeHelper::defaultSize($sqlTypeValue); ?>"
                                            data-max-size="<?php echo (int) StorageColumnTypeHelper::maxSize($sqlTypeValue); ?>"
                                            <?php echo $sqlTypeValue === $sqlType ? 'selected' : ''; ?>
                                        >
                                            <?php echo htmlspecialchars($sqlTypeOptionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else : ?>
                                <span title="<?php echo htmlspecialchars(StorageColumnTypeHelper::sqlDefinition($sqlType, $fieldSize), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo $sqlTypeLabel; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap" data-cb-storage-col="field_size">
                            <?php if ($rowSizeEditable && StorageColumnTypeHelper::supportsSize($sqlType)) : ?>
                                <input
                                    type="number"
                                    min="1"
                                    max="<?php echo (int) StorageColumnTypeHelper::maxSize($sqlType); ?>"
                                    class="form-control form-control-sm cb-storage-field-size-input<?php echo htmlspecialchars($rowFieldEditable ? ' cb-storage-field-editable' : '', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-field-id="<?php echo $id; ?>"
                                    data-sql-type="<?php echo htmlspecialchars($sqlType, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-previous-value="<?php echo htmlspecialchars((string) (int) $fieldSize, ENT_QUOTES, 'UTF-8'); ?>"
                                    style="width:6rem;"
                                    title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_SIZE_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                    value="<?php echo (int) $fieldSize; ?>"
                                    <?php echo htmlspecialchars($rowFieldEditable ? 'disabled' : '', ENT_QUOTES, 'UTF-8'); ?>
                                >
                            <?php elseif (StorageColumnTypeHelper::supportsSize($sqlType)) : ?>
                                <?php echo (int) $fieldSize; ?>
                            <?php else : ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td class="text-center" data-cb-storage-col="required">
                            <?php if ($rowRequiredEditable) : ?>
                                <button type="button"
                                    class="btn btn-sm btn-link p-0 cb-storage-field-required-toggle"
                                    data-field-id="<?php echo $id; ?>"
                                    data-required="<?php echo $required ? '1' : '0'; ?>"
                                    data-yes-title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_REQUIRED_YES_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-no-title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_REQUIRED_NO_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                    title="<?php echo htmlspecialchars($requiredTipText, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top">
                                    <span class="cb-storage-field-required-icon <?php echo $requiredIconClass; ?>" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?php echo htmlspecialchars($requiredTipText, ENT_QUOTES, 'UTF-8'); ?></span>
                                </button>
                            <?php else : ?>
                                <span class="<?php echo $requiredIconClass; ?>" title="<?php echo htmlspecialchars($requiredTipText, ENT_QUOTES, 'UTF-8'); ?>" data-bs-toggle="tooltip" data-bs-placement="top" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo htmlspecialchars($requiredTipText, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td data-cb-storage-col="group">
                            <input type="hidden" name="itemNames[<?php echo $id; ?>]" value="<?php echo $name; ?>" />
                            <input type="hidden" name="itemTitles[<?php echo $id; ?>]" value="<?php echo $title; ?>" />

                            <?php if ($isSystemField) : ?>
                                &mdash;
                            <?php else : ?>
                                <input class="form-check-input" type="radio"
                                    name="itemIsGroup[<?php echo $id; ?>]"
                                    value="1"
                                    id="itemIsGroup_<?php echo $id; ?>"
                                    <?php echo $isGroup ? 'checked="checked"' : ''; ?> />
                                <label for="itemIsGroup_<?php echo $id; ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDERNG_YES'); ?>
                                </label>

                                <input class="form-check-input" type="radio"
                                    name="itemIsGroup[<?php echo $id; ?>]"
                                    value="0"
                                    id="itemIsGroupNo_<?php echo $id; ?>"
                                    <?php echo !$isGroup ? 'checked="checked"' : ''; ?> />
                                <label for="itemIsGroupNo_<?php echo $id; ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDERNG_NO'); ?>
                                </label>

                                <div id="itemGroupDefinitions_<?php echo $id; ?>" data-cb-group-definition-edit<?php echo $isGroup ? '' : ' hidden'; ?>>
                                    <button type="button" class="btn btn-link btn-sm p-0"
                                        title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_GROUP_DEFINITION_EDIT_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                        onclick="document.getElementById('itemGroupDefinitions<?php echo $id; ?>').style.display='block'; this.parentNode.style.display='none'; document.getElementById('itemGroupDefinitions<?php echo $id; ?>').focus(); return false;">
                                        [<?php echo Text::_('COM_CONTENTBUILDERNG_EDIT'); ?>]
                                    </button>
                                </div>
                                <textarea class="form-control form-control-sm mt-1"
                                    onblur="this.style.display='none'; document.getElementById('itemGroupDefinitions_<?php echo $id; ?>').style.display='block';"
                                    id="itemGroupDefinitions<?php echo $id; ?>"
                                    style="display:none; width:100%; height:50px;"
                                    name="itemGroupDefinitions[<?php echo $id; ?>]"><?php echo $groupDefinition; ?></textarea>
                            <?php endif; ?>
                        </td>
                        <td class="order cb-order-col" data-cb-storage-col="order">
                            <?php if ($ordering) : ?>
                                <span class="cb-order-position"><?php echo (int) ($row->ordering ?? 0); ?></span>
                                <input type="hidden"
                                    name="order[]"
                                    value="<?php echo (int) ($row->ordering ?? 0); ?>"
                                    class="cb-storage-fields-order-input" />
                            <?php endif; ?>
                        </td>
                        <td class="text-center" data-cb-storage-col="publish"><?php echo $published; ?></td>
                        <td class="text-center text-nowrap" data-cb-storage-col="actions">
                            <?php
                            // Un champ système (ni supprimable ni réordonnable) et une
                            // table externe en lecture seule n'ont aucune action.
                            $rowCanDelete = (int) ($item->bytable ?? 0) !== 2 && !$isSystemField;
                            ?>
                            <?php if ($rowCanDelete) : ?>
                                <div class="btn-group btn-group-sm cb-storage-field-actions" role="group">
                                    <?php if ($rowFieldEditable) : ?>
                                        <button type="button"
                                            class="btn btn-secondary cb-storage-field-edit hasTooltip"
                                            data-field-id="<?php echo (int) $id; ?>"
                                            aria-expanded="false"
                                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_EDIT'), ENT_QUOTES, 'UTF-8'); ?>"
                                            aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_EDIT'), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top">
                                            <span class="fa-solid fa-pencil" aria-hidden="true"></span>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button"
                                        class="btn btn-danger group-remove cb-storage-field-delete hasTooltip"
                                        title="<?php echo htmlspecialchars(Text::_('JACTION_DELETE'), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        onclick="return cbDeleteStorageField('cb<?php echo (int) $i; ?>');"
                                    >
                                        <span class="fa-solid fa-trash" aria-hidden="true"></span>
                                    </button>
                                    <?php if ($ordering) : ?>
                                        <button type="button"
                                            class="btn btn-primary cb-storage-fields-drag-handle"
                                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_DRAG_TO_REORDER'), ENT_QUOTES, 'UTF-8'); ?>"
                                            aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_DRAG_TO_REORDER'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <span class="icon-arrows-alt" aria-hidden="true"></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

                <tfoot>
                    <?php if ($recordsCount !== null) : ?>
                        <tr>
                            <td colspan="7" class="text-muted small">
                                <?php echo Text::sprintf('COM_CONTENTBUILDERNG_STORAGE_RECORDS_COUNT_FOOTER', (int) $recordsCount); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                            <td colspan="7">
                            <div class="cb-storage-pagination">
                                <div class="cbPagesCounter d-flex flex-wrap align-items-center gap-2">
                                    <?php if ($pagination) {
                                        echo $pagination->getPagesCounter();
                                    } ?>
                                    <?php
                                    echo '<span>' . Text::_('COM_CONTENTBUILDERNG_DISPLAY_NUM') . '&nbsp;</span>';
                                    echo '<div class="d-inline-block">' . ($pagination ? $pagination->getLimitBox() : '') . '</div>';
                                    ?>
                                </div>
                                <div class="cb-storage-pages">
                                    <?php if ($pagination) {
                                        echo $pagination->getPagesLinks();
                                    } ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
