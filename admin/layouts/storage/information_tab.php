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
use Joomla\CMS\Session\Session;

$item = $displayData['item'] ?? null;
$storageId = (int) ($displayData['storageId'] ?? 0);
$tables = (array) ($displayData['tables'] ?? []);
$renderCheckbox = $displayData['renderCheckbox'] ?? null;
$csvToggleTooltip = (string) ($displayData['csvToggleTooltip'] ?? '');
$storageTableExists = $displayData['storageTableExists'] ?? null;
$storageTableLookupName = trim((string) ($displayData['storageTableLookupName'] ?? ''));
$storageTableErrorMessage = trim((string) ($displayData['storageTableErrorMessage'] ?? ''));
$publishedToggleHtml = (string) ($displayData['publishedToggleHtml'] ?? '');
$publishedIconClass = (string) ($displayData['publishedIconClass'] ?? '');
$publishedIconTitle = (string) ($displayData['publishedIconTitle'] ?? '');
$dataTableName = (string) ($displayData['dataTableName'] ?? '-');
$storageModeKey = (string) ($displayData['storageModeKey'] ?? '');
$recordsCount = $displayData['recordsCount'] ?? null;
$createdBy = trim((string) ($displayData['createdBy'] ?? ''));
$modifiedBy = trim((string) ($displayData['modifiedBy'] ?? ''));
$formatDate = $displayData['formatDate'] ?? null;
?>
<?php if ($storageTableExists === false) : ?>
    <div class="alert alert-danger mb-3" role="alert">
        <strong><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_MISSING_TABLE'); ?></strong><br>
        <?php echo htmlspecialchars($storageTableErrorMessage !== '' ? $storageTableErrorMessage : Text::_('COM_CONTENTBUILDERNG_STORAGE_TABLE_NOT_FOUND'), ENT_QUOTES, 'UTF-8'); ?>
        <?php if ($storageTableLookupName !== '') : ?>
            <br><code><?php echo htmlspecialchars($storageTableLookupName, ENT_QUOTES, 'UTF-8'); ?></code>
        <?php endif; ?>
    </div>
<?php elseif ($storageTableErrorMessage !== '') : ?>
    <div class="alert alert-warning mb-3" role="alert">
        <?php echo htmlspecialchars($storageTableErrorMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="mb-2 d-flex justify-content-between align-items-center">
    <span class="badge bg-body-tertiary text-body border">
        <?php echo Text::_('COM_CONTENTBUILDERNG_ID'); ?> #<?php echo (int) ($item->id ?? 0); ?>
    </span>
    <span class="badge bg-body-tertiary text-body border">
        <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_RECORDS_COUNT'); ?> : <?php echo $recordsCount === null ? '-' : (int) $recordsCount; ?>
    </span>
</div>

<div class="card border rounded-3 mb-3">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <tbody>
                <tr>
                    <th scope="row"><label for="name"><?php echo Text::_('COM_CONTENTBUILDERNG_NAME'); ?></label></th>
                    <td colspan="3">
                        <?php if (!$item->bytable) : ?>
                            <input class="form-control form-control-sm" type="text" id="name" name="jform[name]"
                                value="<?php echo htmlspecialchars($item->name ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        <?php else : ?>
                            <input type="hidden" id="name" name="jform[name]"
                                value="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?>" />
                            <?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>

                        <?php if ($item->id && $item->bytable) : ?>
                            <input type="hidden" id="bytable" name="jform[bytable]"
                                value="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?>" />
                        <?php elseif ($item->id) : ?>
                            <input type="hidden" id="bytable" name="jform[bytable]" value="" />
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!$item->id) : ?>
                    <tr>
                        <td colspan="4">
                            <div class="row gy-3">
                                <div class="col-md-6">
                                    <?php if (!empty($tables)) : ?>
                                        <label class="form-label" for="bytable">
                                            <b><?php echo Text::_('COM_CONTENTBUILDERNG_CHOOSE_TABLE'); ?></b>
                                        </label>
                                        <select class="form-select form-select-sm"
                                            onchange="if(this.selectedIndex != 0){ document.getElementById('name').disabled = true; var csvHead = document.getElementById('csvUploadHead'); var csvBody = document.getElementById('csvUpload'); if (csvHead) { csvHead.style.display = 'none'; } if (csvBody) { csvBody.style.display = 'none'; } alert('<?php echo addslashes(Text::_('COM_CONTENTBUILDERNG_CUSTOM_STORAGE_MSG')); ?>'); }else{ document.getElementById('name').disabled = false; var csvHead = document.getElementById('csvUploadHead'); var csvBody = document.getElementById('csvUpload'); if (csvHead) { csvHead.style.display = ''; } }"
                                            name="jform[bytable]" id="bytable">
                                            <option value=""> -
                                                <?php echo Text::_('COM_CONTENTBUILDERNG_NONE'); ?> -
                                            </option>
                                            <?php foreach ($tables as $table) : ?>
                                                <option value="<?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else : ?>
                                        <input type="hidden" id="bytable" name="jform[bytable]" value="" />
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><b><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_CREATE_FROM_FILE'); ?></b></label>
                                    <br />
                                    <button
                                        type="button"
                                        id="csvToggleButton"
                                        class="btn btn-primary mb-2"
                                        onclick="return toggleCsvUploadOptions();"
                                        title="<?php echo htmlspecialchars($csvToggleTooltip, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        aria-controls="csvUpload"
                                        aria-expanded="false"
                                        data-cb-default-text="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV'), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-cb-create-text="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_CREATE_FROM_FILE'), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-cb-new-storage="1"
                                        data-cb-preview-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_PREVIEW_FROM_FILE'), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-cb-token="<?php echo Session::getFormToken(); ?>"
                                    >
                                        <i class="fa fa-file-excel me-1" aria-hidden="true"></i>
                                        <span class="cb-csv-button-label"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_CREATE_FROM_FILE'); ?></span>
                                    </button>
                                    <div id="csvUploadHead"></div>
                                    <div style="display: none;" id="csvUpload" class="cb-csv-upload-panel">
                                        <input size="9" type="file" id="csv_file" name="csv_file" accept=".csv,.xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
                                        <br />
                                        Max.
                                        <?php
                                        $max_upload = (int) (ini_get('upload_max_filesize'));
                                        $max_post = (int) (ini_get('post_max_size'));
                                        $memory_limit = (int) (ini_get('memory_limit'));
                                        $upload_mb = min($max_upload, $max_post, $memory_limit);
                                        $val = trim((string) $upload_mb);
                                        $last = strtolower($val[strlen($val) - 1] ?? '');
                                        switch ($last) {
                                            case 'g':
                                                $val .= ' GB';
                                                break;
                                            case 'k':
                                                $val .= ' kb';
                                                break;
                                            default:
                                                $val .= ' MB';
                                        }
                                        echo $val;
                                        ?>
                                        <br />
                                        <br />
                                        <label for="csv_drop_records">
                                            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_DROP_RECORDS'); ?>
                                        </label> <?php echo is_callable($renderCheckbox) ? $renderCheckbox('jform[csv_drop_records]', 'csv_drop_records', true) : ''; ?>
                                        <br />
                                        <label for="csv_published">
                                            <?php echo Text::_('COM_CONTENTBUILDERNG_AUTO_PUBLISH'); ?>
                                        </label> <?php echo is_callable($renderCheckbox) ? $renderCheckbox('jform[csv_published]', 'csv_published', true) : ''; ?>
                                        <br />
                                        <label for="csv_import_data">
                                            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_CSV_IMPORT_DATA'); ?>
                                        </label> <?php echo is_callable($renderCheckbox) ? $renderCheckbox('jform[csv_import_data]', 'csv_import_data', true) : ''; ?>
                                        <br />
                                        <label for="csv_delimiter">
                                            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_DELIMITER'); ?>
                                        </label> <input class="form-control form-control-sm" maxlength="3" type="text"
                                            size="1" id="csv_delimiter" name="jform[csv_delimiter]" value="," />
                                        <br />
                                        <br />
                                        <label class="editlinktip hasTip"
                                            title="<?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_REPAIR_ENCODING_TIP'); ?>"
                                            for="csv_repair_encoding">
                                            <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_REPAIR_ENCODING'); ?>*
                                        </label>
                                        <br />
                                        <select class="form-select-sm" style="width: 150px;" name="jform[csv_repair_encoding]"
                                            id="csv_repair_encoding">
                                            <option value=""> -
                                                <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_NO_REPAIR_ENCODING'); ?>
                                                -
                                            </option>
                                            <option value="WINDOWS-1250">WINDOWS-1250</option>
                                            <option value="WINDOWS-1251">WINDOWS-1251</option>
                                            <option value="WINDOWS-1252">WINDOWS-1252 (ANSI)</option>
                                            <option value="WINDOWS-1253">WINDOWS-1253</option>
                                            <option value="WINDOWS-1254">WINDOWS-1254</option>
                                            <option value="WINDOWS-1255">WINDOWS-1255</option>
                                            <option value="WINDOWS-1256">WINDOWS-1256</option>
                                            <option value="ISO-8859-1">ISO-8859-1 (LATIN1)</option>
                                            <option value="ISO-8859-2">ISO-8859-2</option>
                                            <option value="ISO-8859-3">ISO-8859-3</option>
                                            <option value="ISO-8859-4">ISO-8859-4</option>
                                            <option value="ISO-8859-5">ISO-8859-5</option>
                                            <option value="ISO-8859-6">ISO-8859-6</option>
                                            <option value="ISO-8859-7">ISO-8859-7</option>
                                            <option value="ISO-8859-8">ISO-8859-8</option>
                                            <option value="ISO-8859-9">ISO-8859-9</option>
                                            <option value="ISO-8859-10">ISO-8859-10</option>
                                            <option value="ISO-8859-11">ISO-8859-11</option>
                                            <option value="ISO-8859-12">ISO-8859-12</option>
                                            <option value="ISO-8859-13">ISO-8859-13</option>
                                            <option value="ISO-8859-14">ISO-8859-14</option>
                                            <option value="ISO-8859-15">ISO-8859-15 (LATIN-9)</option>
                                            <option value="ISO-8859-16">ISO-8859-16</option>
                                            <option value="UTF-8-MAC">UTF-8-MAC</option>
                                            <option value="UTF-16">UTF-16</option>
                                            <option value="UTF-16BE">UTF-16BE</option>
                                            <option value="UTF-16LE">UTF-16LE</option>
                                            <option value="UTF-32">UTF-32</option>
                                            <option value="UTF-32BE">UTF-32BE</option>
                                            <option value="UTF-32LE">UTF-32LE</option>
                                            <option value="ASCII">ASCII</option>
                                            <option value="BIG-5">BIG-5</option>
                                            <option value="HEBREW">HEBREW</option>
                                            <option value="CYRILLIC">CYRILLIC</option>
                                            <option value="ARABIC">ARABIC</option>
                                            <option value="GREEK">GREEK</option>
                                            <option value="CHINESE">CHINESE</option>
                                            <option value="KOREAN">KOREAN</option>
                                            <option value="KOI8-R">KOI8-R</option>
                                            <option value="KOI8-U">KOI8-U</option>
                                            <option value="KOI8-RU">KOI8-RU</option>
                                            <option value="EUC-JP">EUC-JP</option>
                                        </select>
                                        <div id="cbCsvPreviewPanel" class="cb-csv-preview-panel" style="display:none;">
                                            <div class="cb-csv-preview-head"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_PREVIEW_FROM_FILE'); ?></div>
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width:10%;">
                                                            <input type="checkbox" class="form-check-input cb-csv-select-all" id="cbCsvSelectAll" checked
                                                                title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_CSV_SELECT_ALL_COLUMNS'), ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-bs-toggle="tooltip" data-bs-placement="top" />
                                                        </th>
                                                        <th style="width:35%;"><?php echo Text::_('COM_CONTENTBUILDERNG_NAME'); ?></th>
                                                        <th style="width:40%;"><?php echo Text::_('COM_CONTENTBUILDERNG_LIST_STATES_TITLE'); ?></th>
                                                        <th style="width:15%;"><?php echo Text::_('JSTATUS'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="cbCsvPreviewBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th scope="row"><label for="title"><?php echo Text::_('COM_CONTENTBUILDERNG_LIST_STATES_TITLE'); ?></label></th>
                    <td colspan="3">
                        <input class="form-control form-control-sm" type="text" id="title"
                            name="jform[title]"
                            value="<?php echo htmlspecialchars($item->title ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_TABLE'); ?></th>
                    <td><?php echo htmlspecialchars($dataTableName, ENT_QUOTES, 'UTF-8'); ?></td>
                    <th scope="row"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_MODE'); ?></th>
                    <td>
                        <?php $storageModeBadgeClass = $storageModeKey === 'COM_CONTENTBUILDERNG_STORAGE_MODE_EXTERNAL' ? 'text-bg-warning' : 'text-bg-info'; ?>
                        <span class="badge <?php echo $storageModeBadgeClass; ?>"><?php echo htmlspecialchars(Text::_($storageModeKey), ENT_QUOTES, 'UTF-8'); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php if ((int) ($item->id ?? 0) > 0) : ?>
    <div class="card border rounded-3 mb-3">
        <div class="card-body">
            <label class="form-label"><b><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV'); ?></b></label>
            <br />
            <button
                type="button"
                id="csvToggleButton"
                class="btn btn-primary mb-2"
                onclick="return toggleCsvUploadOptions();"
                title="<?php echo htmlspecialchars($csvToggleTooltip, ENT_QUOTES, 'UTF-8'); ?>"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                aria-controls="csvUpload"
                aria-expanded="false"
                data-cb-default-text="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV'), ENT_QUOTES, 'UTF-8'); ?>"
                data-cb-create-text="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_CREATE_FROM_FILE'), ENT_QUOTES, 'UTF-8'); ?>"
                data-cb-new-storage="0"
                data-cb-preview-label="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_STORAGE_PREVIEW_FROM_FILE'), ENT_QUOTES, 'UTF-8'); ?>"
                data-cb-token="<?php echo Session::getFormToken(); ?>"
            >
                <i class="fa fa-file-excel me-1" aria-hidden="true"></i>
                <span class="cb-csv-button-label"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV'); ?></span>
            </button>
            <div id="csvUploadHead"></div>
            <div style="display: none;" id="csvUpload" class="cb-csv-upload-panel">
                <input size="9" type="file" id="csv_file" name="csv_file" accept=".csv,.xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
                <br />
                Max.
                <?php
                $max_upload = (int) (ini_get('upload_max_filesize'));
                $max_post = (int) (ini_get('post_max_size'));
                $memory_limit = (int) (ini_get('memory_limit'));
                $upload_mb = min($max_upload, $max_post, $memory_limit);
                $val = trim((string) $upload_mb);
                $last = strtolower($val[strlen($val) - 1] ?? '');
                switch ($last) {
                    case 'g':
                        $val .= ' GB';
                        break;
                    case 'k':
                        $val .= ' kb';
                        break;
                    default:
                        $val .= ' MB';
                }
                echo $val;
                ?>
                <br />
                <br />
                <label for="csv_drop_records">
                    <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_DROP_RECORDS'); ?>
                </label> <?php echo is_callable($renderCheckbox) ? $renderCheckbox('jform[csv_drop_records]', 'csv_drop_records', true) : ''; ?>
                <br />
                <label for="csv_published">
                    <?php echo Text::_('COM_CONTENTBUILDERNG_AUTO_PUBLISH'); ?>
                </label> <?php echo is_callable($renderCheckbox) ? $renderCheckbox('jform[csv_published]', 'csv_published', true) : ''; ?>
                <br />
                <label for="csv_import_data">
                    <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_CSV_IMPORT_DATA'); ?>
                </label> <?php echo is_callable($renderCheckbox) ? $renderCheckbox('jform[csv_import_data]', 'csv_import_data', true) : ''; ?>
                <br />
                <label for="csv_delimiter">
                    <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_DELIMITER'); ?>
                </label> <input class="form-control form-control-sm" maxlength="3" type="text"
                    size="1" id="csv_delimiter" name="jform[csv_delimiter]" value="," />
                <br />
                <br />
                <label class="editlinktip hasTip"
                    title="<?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_REPAIR_ENCODING_TIP'); ?>"
                    for="csv_repair_encoding">
                    <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_REPAIR_ENCODING'); ?>*
                </label>
                <br />
                <select class="form-select-sm" style="width: 150px;" name="jform[csv_repair_encoding]"
                    id="csv_repair_encoding">
                    <option value=""> -
                        <?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_UPDATE_FROM_CSV_NO_REPAIR_ENCODING'); ?>
                        -
                    </option>
                    <option value="WINDOWS-1250">WINDOWS-1250</option>
                    <option value="WINDOWS-1251">WINDOWS-1251</option>
                    <option value="WINDOWS-1252">WINDOWS-1252 (ANSI)</option>
                    <option value="WINDOWS-1253">WINDOWS-1253</option>
                    <option value="WINDOWS-1254">WINDOWS-1254</option>
                    <option value="WINDOWS-1255">WINDOWS-1255</option>
                    <option value="WINDOWS-1256">WINDOWS-1256</option>
                    <option value="ISO-8859-1">ISO-8859-1 (LATIN1)</option>
                    <option value="ISO-8859-2">ISO-8859-2</option>
                    <option value="ISO-8859-3">ISO-8859-3</option>
                    <option value="ISO-8859-4">ISO-8859-4</option>
                    <option value="ISO-8859-5">ISO-8859-5</option>
                    <option value="ISO-8859-6">ISO-8859-6</option>
                    <option value="ISO-8859-7">ISO-8859-7</option>
                    <option value="ISO-8859-8">ISO-8859-8</option>
                    <option value="ISO-8859-9">ISO-8859-9</option>
                    <option value="ISO-8859-10">ISO-8859-10</option>
                    <option value="ISO-8859-11">ISO-8859-11</option>
                    <option value="ISO-8859-12">ISO-8859-12</option>
                    <option value="ISO-8859-13">ISO-8859-13</option>
                    <option value="ISO-8859-14">ISO-8859-14</option>
                    <option value="ISO-8859-15">ISO-8859-15 (LATIN-9)</option>
                    <option value="ISO-8859-16">ISO-8859-16</option>
                    <option value="UTF-8-MAC">UTF-8-MAC</option>
                    <option value="UTF-16">UTF-16</option>
                    <option value="UTF-16BE">UTF-16BE</option>
                    <option value="UTF-16LE">UTF-16LE</option>
                    <option value="UTF-32">UTF-32</option>
                    <option value="UTF-32BE">UTF-32BE</option>
                    <option value="UTF-32LE">UTF-32LE</option>
                    <option value="ASCII">ASCII</option>
                    <option value="BIG-5">BIG-5</option>
                    <option value="HEBREW">HEBREW</option>
                    <option value="CYRILLIC">CYRILLIC</option>
                    <option value="ARABIC">ARABIC</option>
                    <option value="GREEK">GREEK</option>
                    <option value="CHINESE">CHINESE</option>
                    <option value="KOREAN">KOREAN</option>
                    <option value="KOI8-R">KOI8-R</option>
                    <option value="KOI8-U">KOI8-U</option>
                    <option value="KOI8-RU">KOI8-RU</option>
                    <option value="EUC-JP">EUC-JP</option>
                </select>
                <div id="cbCsvPreviewPanel" class="cb-csv-preview-panel" style="display:none;">
                    <div class="cb-csv-preview-head"><?php echo Text::_('COM_CONTENTBUILDERNG_STORAGE_PREVIEW_FROM_FILE'); ?></div>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th style="width:40%;"><?php echo Text::_('COM_CONTENTBUILDERNG_NAME'); ?></th>
                                <th style="width:45%;"><?php echo Text::_('COM_CONTENTBUILDERNG_LIST_STATES_TITLE'); ?></th>
                                <th style="width:15%;"><?php echo Text::_('JSTATUS'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="cbCsvPreviewBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card border rounded-3 mb-3">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <tbody>
                <tr class="text-secondary">
                    <th scope="row" style="width: 240px;"><?php echo Text::_('COM_CONTENTBUILDERNG_CREATED_ON'); ?></th>
                    <td><?php echo htmlspecialchars(is_callable($formatDate) ? $formatDate($item->created ?? null) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <th scope="row" style="width: 240px;"><?php echo Text::_('JGLOBAL_FIELD_CREATED_BY_LABEL'); ?></th>
                    <td><?php echo htmlspecialchars($createdBy !== '' ? $createdBy : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr class="text-secondary">
                    <th scope="row"><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL'); ?></th>
                    <td><?php echo htmlspecialchars(is_callable($formatDate) ? $formatDate($item->modified ?? null) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <th scope="row"><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
                    <td><?php echo htmlspecialchars($modifiedBy !== '' ? $modifiedBy : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
