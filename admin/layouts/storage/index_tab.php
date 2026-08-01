<?php

/**
 * @package     ContentBuilderNG
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$item = $displayData['item'] ?? null;
$storageId = (int) ($displayData['storageId'] ?? 0);
/** @var array<int,array{name:string,columns:array<int,string>,unique:bool}> $indexes */
$indexesData = $displayData['indexes'] ?? null;
$indexes = is_array($indexesData) ? $indexesData : [];
/** @var array<int,string> $indexableColumns */
$indexableColumnsData = $displayData['indexableColumns'] ?? null;
$indexableColumns = is_array($indexableColumnsData) ? $indexableColumnsData : [];
$isBytable = (int) ($item->bytable ?? 0) !== 0;
?>
<div class="m-3">
    <?php if ($storageId < 1) : ?>
        <div class="alert alert-info">
            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_SAVE_FIRST_ADD_FIELDS'); ?>
        </div>
    <?php elseif ($isBytable) : ?>
        <div class="alert alert-info">
            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_BYTABLE_UNAVAILABLE'); ?>
        </div>
    <?php else : ?>
        <input type="hidden" id="cb-storage-index-name-input" name="index_name" value="" />

        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_NAME'); ?></th>
                        <th><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_COLUMNS'); ?></th>
                        <th class="text-center"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_UNIQUE'); ?></th>
                        <th width="40"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($indexes)) : ?>
                    <tr>
                        <td colspan="4" class="text-muted">
                            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_NONE'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($indexes as $index) :
                        $indexName = (string) ($index['name'] ?? '');
                        $indexColumns = implode(', ', (array) ($index['columns'] ?? []));
                        $indexUnique = !empty($index['unique']);
                        $indexIsPrimary = !empty($index['primary']);
                        ?>
                        <tr class="<?php echo $indexIsPrimary ? 'text-muted' : ''; ?>" title="<?php echo $indexIsPrimary ? htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_PRIMARY_TIP'), ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <td>
                                <?php echo htmlspecialchars($indexIsPrimary ? Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_PRIMARY_LABEL') : $indexName, ENT_QUOTES, 'UTF-8'); ?>
                                <?php if ($indexIsPrimary) : ?>
                                    <span class="fa-solid fa-key ms-1" aria-hidden="true"></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($indexColumns, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-center">
                                <?php echo Text::_($indexUnique ? 'JYES' : 'JNO'); ?>
                            </td>
                            <td class="text-center">
                                <?php if (!$indexIsPrimary) : ?>
                                    <button type="button"
                                        class="btn btn-sm btn-link text-danger p-0"
                                        title="<?php echo htmlspecialchars(Text::_('JACTION_DELETE'), ENT_QUOTES, 'UTF-8'); ?>"
                                        onclick="cbDeleteStorageIndex('<?php echo htmlspecialchars(addslashes($indexName), ENT_QUOTES, 'UTF-8'); ?>')"
                                    >
                                        <span class="fa-solid fa-trash" aria-hidden="true"></span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($indexableColumns)) : ?>
            <div class="d-flex align-items-end gap-2 mt-3">
                <div>
                    <label class="form-label mb-1" for="cb-storage-index-add-column">
                        <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_ADD_COLUMN_LABEL'); ?>
                    </label>
                    <select class="form-select form-select-sm" id="cb-storage-index-add-column" name="jform[index_column]" style="width:auto; min-width:12rem;">
                        <?php foreach ($indexableColumns as $columnName) : ?>
                            <option value="<?php echo htmlspecialchars($columnName, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($columnName, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button"
                    class="btn btn-success btn-sm"
                    onclick="cbStorageSubmitbutton('storage.addindex')"
                >
                    <span class="fa-solid fa-plus" aria-hidden="true"></span>
                    <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_ADD_BUTTON'); ?>
                </button>
            </div>
        <?php else : ?>
            <div class="alert alert-info mt-3 py-1 px-2 mb-0 d-inline-block">
                <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_INDEX_NO_COLUMN_AVAILABLE'); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
