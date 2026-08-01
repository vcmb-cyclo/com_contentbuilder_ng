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

// Colonnes réservées ajoutées automatiquement à la table physique
// (StorageModel::syncStorageDataTableOrBytable()) mais pas encore exposées
// comme field géré : preview en lecture seule, toujours en fin de liste
// (peu importe le tri courant), sur la dernière page seulement.
$existingFieldNames = [];
foreach ($fields as $fieldRow) {
    $existingFieldNames[(string) ($fieldRow->name ?? '')] = true;
}
$pendingSystemFields = array_diff_key(StorageSystemFieldHelper::definitions(), $existingFieldNames);
$isLastPage = !$pagination || ((int) $pagination->pagesCurrent >= (int) $pagination->pagesTotal);
$showPendingSystemFields = $storageId > 0 && $isLastPage && !empty($pendingSystemFields);
$storageFieldColumns = [
    'id' => Text::_('COM_CONTENTBUILDERNG_ID'),
    'name' => Text::_('COM_CONTENTBUILDERNG_NAME'),
    'title' => Text::_('COM_CONTENTBUILDERNG_LIST_STATES_TITLE'),
    'sql_type' => Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE'),
    'field_size' => Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_SIZE'),
    'group' => Text::_('COM_CONTENTBUILDERNG_STORAGE_GROUP'),
    'order' => Text::_('COM_CONTENTBUILDERNG_ORDERBY'),
    'publish' => Text::_('COM_CONTENTBUILDERNG_LIST_STATES_PUBLISHED'),
];
?>
<table width="100%">
    <tr>
        <td class="align-top" style="width: 200px;">
            <?php if (!$item->bytable && $item->id) : ?>
                <script type="application/json" id="cb-storage-field-sql-types"><?php echo json_encode($storageSqlTypeOptions, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
            <?php endif; ?>
        </td>

        <td class="align-top">
            <div class="d-flex justify-content-between align-items-center m-3 mb-2">
                <div>
                    <?php if (!$item->bytable && $item->id) : ?>
                        <button type="button"
                            id="cb-storage-field-add-button"
                            class="btn btn-success btn-sm hasTooltip"
                            title="<?php echo htmlspecialchars($addFieldTooltip, ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top">
                            <span class="fa-solid fa-plus" aria-hidden="true"></span>
                            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_ADD_FIELD_BUTTON'); ?>
                        </button>
                    <?php elseif (!$item->bytable) : ?>
                        <div class="alert alert-info py-1 px-2 mb-0 d-inline-block">
                            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_SAVE_FIRST_ADD_FIELDS'); ?>
                        </div>
                    <?php endif; ?>
                </div>
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
            <table class="table table-striped m-3 cb-storage-fields-table" data-cb-storage-fields-ordering="<?php echo $ordering ? '1' : '0'; ?>">
                <thead>
                    <tr>
                        <th width="20" data-cb-storage-col="check">
                            <input class="form-check-input" type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);" aria-label="<?php echo htmlspecialchars(Text::_('JGLOBAL_CHECK_ALL'), ENT_QUOTES, 'UTF-8'); ?>">
                        </th>
                        <th width="60" class="text-nowrap" data-cb-storage-col="id">
                            <?php echo Text::_('COM_CONTENTBUILDERNG_ID'); ?>
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
                            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_SIZE'); ?>
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
                        <th width="40" data-cb-storage-col="actions"></th>
                    </tr>
                </thead>
                <tbody id="cb-storage-fields-tbody">
                <?php $n = $fieldsCount; ?>
                <?php foreach ($fields as $i => $row) :
                    $id = (int) ($row->id ?? 0);
                    $rawName = (string) ($row->name ?? '');
                    $name = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
                    $title = htmlspecialchars((string) ($row->title ?? ''), ENT_QUOTES, 'UTF-8');
                    $sqlType = StorageColumnTypeHelper::normalize((string) ($row->sql_type ?? StorageColumnTypeHelper::DEFAULT_TYPE));
                    $sqlTypeLabel = htmlspecialchars(StorageColumnTypeHelper::label($sqlType), ENT_QUOTES, 'UTF-8');
                    $fieldSize = StorageColumnTypeHelper::normalizeSize($sqlType, $row->field_size ?? null);
                    $groupDefinition = htmlspecialchars((string) ($row->group_definition ?? ''), ENT_QUOTES, 'UTF-8');
                    $isGroup = !empty($row->is_group);
                    $checked = '<input class="form-check-input" type="checkbox" id="cb' . (int) $i . '" name="cid[]" value="' . $id . '" onclick="Joomla.isChecked(this.checked);">';
                    $published = ContentbuilderngHelper::listPublish('storage', $row, $i);
                    $isSystemField = StorageSystemFieldHelper::isSystemFieldName($rawName);
                    $rowSqlTypeEditable = $canEditSqlType && !$isSystemField;
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" data-cb-row-id="<?php echo $id; ?>" data-cb-item-label="<?php echo $title !== '' ? $title : $name; ?>">
                        <td class="text-center" data-cb-storage-col="check"><?php echo $checked; ?></td>
                        <td class="text-nowrap" data-cb-storage-col="id"><?php echo $id; ?></td>
                        <td data-cb-storage-col="name">
                            <?php echo $name; ?>
                            <?php if ($isSystemField) : ?>
                                <span class="fa-solid fa-gear ms-1" aria-hidden="true" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_MANAGED_TIP'), ENT_QUOTES, 'UTF-8'); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td data-cb-storage-col="title"><?php echo $title; ?></td>
                        <td data-cb-storage-col="sql_type">
                            <?php if ($rowSqlTypeEditable) : ?>
                                <select class="form-select form-select-sm cb-storage-field-type-select" data-field-id="<?php echo $id; ?>" style="width:auto; max-width:12rem;" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_SQL_TYPE_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php foreach ($storageSqlTypeOptions as $sqlTypeValue => $sqlTypeOptionLabel) : ?>
                                        <option
                                            value="<?php echo htmlspecialchars($sqlTypeValue, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-supports-size="<?php echo StorageColumnTypeHelper::supportsSize($sqlTypeValue) ? '1' : '0'; ?>"
                                            data-default-size="<?php echo (int) StorageColumnTypeHelper::defaultSize($sqlTypeValue); ?>"
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
                            <?php if ($rowSqlTypeEditable && StorageColumnTypeHelper::supportsSize($sqlType)) : ?>
                                <input
                                    type="number"
                                    min="1"
                                    class="form-control form-control-sm cb-storage-field-size-input"
                                    data-field-id="<?php echo $id; ?>"
                                    style="width:6rem;"
                                    title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_FIELD_SIZE_TIP'), ENT_QUOTES, 'UTF-8'); ?>"
                                    value="<?php echo (int) $fieldSize; ?>"
                                >
                            <?php elseif (StorageColumnTypeHelper::supportsSize($sqlType)) : ?>
                                <?php echo (int) $fieldSize; ?>
                            <?php else : ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td data-cb-storage-col="group">
                            <input type="hidden" name="itemNames[<?php echo $id; ?>]" value="<?php echo $name; ?>" />
                            <input type="hidden" name="itemTitles[<?php echo $id; ?>]" value="<?php echo $title; ?>" />

                            <?php if ($rawName === 'id') : ?>
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

                                <div id="itemGroupDefinitions_<?php echo $id; ?>">
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
                                <span class="cb-order-icons">
                                    <span>
                                        <?php echo $pagination ? $pagination->orderUpIcon($i, true, 'storage.orderup', 'JLIB_HTML_MOVE_UP', $ordering) : ''; ?>
                                    </span>
                                    <span>
                                        <?php echo $pagination ? $pagination->orderDownIcon($i, $n, true, 'storage.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering) : ''; ?>
                                    </span>
                                    <span>
                                        <button type="button"
                                            class="btn btn-sm btn-link p-0 cb-storage-fields-drag-handle"
                                            title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_DRAG_TO_REORDER'), ENT_QUOTES, 'UTF-8'); ?>"
                                            aria-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_DRAG_TO_REORDER'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <span class="fa-solid fa-grip-lines" aria-hidden="true"></span>
                                        </button>
                                    </span>
                                </span>
                                <input type="hidden"
                                    name="order[]"
                                    value="<?php echo (int) ($row->ordering ?? 0); ?>"
                                    class="cb-storage-fields-order-input" />
                            <?php endif; ?>
                        </td>
                        <td class="text-center" data-cb-storage-col="publish"><?php echo $published; ?></td>
                        <td class="text-center" data-cb-storage-col="actions">
                            <?php if ((int) ($item->bytable ?? 0) !== 2 && !$isSystemField) : ?>
                                <button type="button"
                                    class="btn btn-sm btn-link text-danger p-0 cb-storage-field-delete"
                                    title="<?php echo htmlspecialchars(Text::_('JACTION_DELETE'), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    onclick="return cbDeleteStorageField('cb<?php echo (int) $i; ?>');"
                                >
                                    <span class="fa-solid fa-trash" aria-hidden="true"></span>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($showPendingSystemFields) : ?>
                    <?php foreach ($pendingSystemFields as $pendingFieldName => $pendingFieldDefinition) : ?>
                        <tr class="cb-storage-field-system-preview text-muted" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_SYSTEM_FIELD_PREVIEW_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <td class="text-center" data-cb-storage-col="check">
                                <input class="form-check-input" type="checkbox" disabled>
                            </td>
                            <td class="text-nowrap" data-cb-storage-col="id">—</td>
                            <td data-cb-storage-col="name">
                                <?php echo htmlspecialchars($pendingFieldName, ENT_QUOTES, 'UTF-8'); ?>
                                <span class="fa-solid fa-gear ms-1" aria-hidden="true"></span>
                            </td>
                            <td data-cb-storage-col="title"><?php echo htmlspecialchars((string) $pendingFieldDefinition['label'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-cb-storage-col="sql_type">
                                <?php echo htmlspecialchars(StorageColumnTypeHelper::label((string) $pendingFieldDefinition['sql_type']), ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td class="text-nowrap" data-cb-storage-col="field_size">—</td>
                            <td data-cb-storage-col="group">—</td>
                            <td class="cb-order-col" data-cb-storage-col="order">—</td>
                            <td class="text-center" data-cb-storage-col="publish">
                                <button type="button"
                                    class="btn btn-sm btn-link p-0"
                                    title="<?php echo htmlspecialchars(Text::_('JLIB_HTML_PUBLISH_ITEM'), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    onclick="document.getElementById('cb-system-field-name').value='<?php echo htmlspecialchars($pendingFieldName, ENT_QUOTES, 'UTF-8'); ?>'; Joomla.submitbutton('storage.publishSystemField'); return false;">
                                    <span class="fa-solid fa-circle-xmark text-danger" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?php echo Text::_('JLIB_HTML_PUBLISH_ITEM'); ?></span>
                                </button>
                            </td>
                            <td class="text-center" data-cb-storage-col="actions"></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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
        </td>
    </tr>
</table>
