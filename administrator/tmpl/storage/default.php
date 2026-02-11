<?php

/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$listOrder = $this->state ? (string) $this->state->get('list.ordering', 'ordering') : 'ordering';
$listDirn  = $this->state ? (string) $this->state->get('list.direction', 'asc') : 'asc';
$listDirn  = strtolower($listDirn) === 'desc' ? 'desc' : 'asc';
$storageId = (int) ($this->item->id ?? 0);
$limitValue = (int) $this->state?->get('list.limit', 0);

$sortFields = ['id', 'name', 'title', 'group_definition', 'ordering', 'published'];
$sortLinks = [];

foreach ($sortFields as $field) {
    $isActive = ($listOrder === $field);
    $nextDir = ($isActive && $listDirn === 'asc') ? 'desc' : 'asc';
    $indicator = '';

    if ($isActive) {
        $indicator = ($listDirn === 'asc')
            ? ' <span class="ms-1 icon-sort icon-sort-asc" aria-hidden="true"></span>'
            : ' <span class="ms-1 icon-sort icon-sort-desc" aria-hidden="true"></span>';
    }

    $sortLinks[$field] = [
        'url' => \Joomla\CMS\Router\Route::_(
            'index.php?option=com_contentbuilder_ng&task=storage.display&layout=edit&id='
            . $storageId
            . '&list[start]=0'
            . '&list[ordering]=' . $field
            . '&list[direction]=' . $nextDir
            . '&list[limit]=' . max(0, $limitValue),
            false
        ),
        'indicator' => $indicator,
    ];
}

?>

<script>
function listItemTask(id, task) {
    var form = document.getElementById('adminForm');
    if (!form) return false;

    form.querySelectorAll('input[type="checkbox"][name^="cb"]').forEach(function (cb) {
        cb.checked = false;
    });

    var target = form.querySelector('#' + CSS.escape(id)) || form.elements[id];
    if (!target) return false;

    target.checked = true;
    var boxchecked = form.querySelector('input[name="boxchecked"]');
    if (boxchecked) {
        boxchecked.value = 1;
    }

    Joomla.submitform(task, form);
    return false;
}

if (typeof Joomla !== 'undefined') {
    Joomla.listItemTask = listItemTask;
}
</script>

<form action="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_contentbuilder_ng&task=storage.edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

<?php
// Démarrer les onglets
echo HTMLHelper::_('uitab.startTabSet', 'view-pane', ['active' => 'tab0']);
// Premier onglet
echo HTMLHelper::_('uitab.addTab', 'view-pane', 'tab0', Text::_('COM_CONTENTBUILDER_NG_STORAGE'));
?>

<table width="100%">
        <tr>
            <td width="200" valign="top">

                <fieldset class="adminform">
                    <table width="100%">
                        <tr>
                            <td style="min-width: 150px;">
                                <label for="name">
                                    <b>
                                        <?php echo Text::_('COM_CONTENTBUILDER_NG_NAME'); ?>
                                    </b>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                if (!$this->item->bytable) {
                                ?>
                                    <input class="form-control form-control-sm w-100" type="text" id="name" name="jform[name]"
                                        value="<?php echo htmlentities($this->item->name ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                    <br /><br />
                                <?php
                                } else {
                                ?>
                                    <input type="hidden" id="name" name="jform[name]"
                                        value="<?php echo htmlentities($this->item->name, ENT_QUOTES, 'UTF-8'); ?>" />
                                <?php
                                }

                                if (!$this->item->id) {
                                ?>
                                    <b>
                                        <?php echo Text::_('COM_CONTENTBUILDER_NG_CHOOSE_TABLE'); ?>
                                    </b>
                                    <br />
                                    <select class="form-select-sm"
                                        onchange="if(this.selectedIndex != 0){ document.getElementById('name').disabled = true; document.getElementById('csvUploadHead').style.display = 'none'; document.getElementById('csvUpload').style.display = 'none'; alert('<?php echo addslashes(Text::_('COM_CONTENTBUILDER_NG_CUSTOM_STORAGE_MSG')); ?>'); }else{ document.getElementById('name').disabled = false; document.getElementById('csvUploadHead').style.display = ''; document.getElementById('csvUpload').style.display = ''; }"
                                        name="jform[bytable]" id="bytable" style="max-width: 150px;">
                                        <option value=""> -
                                            <?php echo Text::_('COM_CONTENTBUILDER_NG_NONE'); ?> -
                                        </option>
                                        <?php
                                        foreach ($this->tables as $table) {
                                        ?>
                                            <option value="<?php echo htmlentities($table, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlentities($table, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                <?php
                                } else if ($this->item->bytable) {
                                ?>
                                    <input type="hidden" id="bytable" name="jform[bytable]"
                                        value="<?php echo htmlentities($this->item->name, ENT_QUOTES, 'UTF-8'); ?>" />
                                    <?php echo htmlentities($this->item->name, ENT_QUOTES, 'UTF-8'); ?>
                                <?php
                                } else if (!$this->item->bytable) {
                                ?>
                                    <input type="hidden" id="bytable" name="jform[bytable]" value="" />
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td width="100">
                                <label for="title">
                                    <b>
                                        <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_TITLE'); ?>
                                    </b>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input class="form-control form-control-sm w-100" type="text" id="title"
                                    name="jform[title]"
                                    value="<?php echo htmlentities($this->item->title ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                            </td>
                        </tr>
                        <tr id="csvUploadHead">
                            <td width="100">
                                <br />
                                <div class="mb-3"
                                    onclick="if(document.getElementById('csvUpload').style.display == 'none'){document.getElementById('csvUpload').style.display='';}else{document.getElementById('csvUpload').style.display='none'}"
                                    style="cursor:pointer;">
                                    <b>
                                        <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_UPDATE_FROM_CSV'); ?>
                                    </b>
                                </div>
                            </td>
                        </tr>
                        <tr style="display: none;" id="csvUpload">
                            <td>
                                <input size="9" type="file" id="csv_file" name="csv_file" />
                                <br />
                                Max.
                                <?php
                                $max_upload = (int) (ini_get('upload_max_filesize'));
                                $max_post = (int) (ini_get('post_max_size'));
                                $memory_limit = (int) (ini_get('memory_limit'));
                                $upload_mb = min($max_upload, $max_post, $memory_limit);
                                $val = trim($upload_mb);
                                $last = strtolower($val[strlen($val) - 1]);
                                switch ($last) {
                                    // The 'G' modifier is available since PHP 5.1.0
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
                                    <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_UPDATE_FROM_CSV_DROP_RECORDS'); ?>
                                </label> <input class="form-check-input" type="checkbox" id="csv_drop_records"
                                    name="jform[csv_drop_records]" value="1" checked="checked" />
                                <br />
                                <label for="csv_published">
                                    <?php echo Text::_('COM_CONTENTBUILDER_NG_AUTO_PUBLISH'); ?>
                                </label> <input class="form-check-input" type="checkbox" id="csv_published"
                                    name="jform[csv_published]" value="1" checked="checked" />
                                <br />
                                <label for="csv_delimiter">
                                    <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_UPDATE_FROM_CSV_DELIMITER'); ?>
                                </label> <input class="form-control form-control-sm" maxlength="3" type="text"
                                    size="1" id="csv_delimiter" name="jform[csv_delimiter]" value="," />
                                <br />
                                <br />
                                <label class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_UPDATE_FROM_CSV_REPAIR_ENCODING_TIP'); ?>"
                                    for="csv_repair_encoding">
                                    <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_UPDATE_FROM_CSV_REPAIR_ENCODING'); ?>*
                                </label>
                                <br />
                                <select class="form-select-sm" style="width: 150px;" name="jform[csv_repair_encoding]"
                                    id="csv_repair_encoding">
                                    <option value=""> -
                                        <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_UPDATE_FROM_CSV_NO_REPAIR_ENCODING'); ?>
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
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <?php
                if (!$this->item->bytable) {
                ?>
                    <fieldset class="adminform">
                    <?php if ((int) $this->item->id === 0) : ?>
                    <div class="alert alert-info">
                        Enregistrez d’abord le stockage, puis vous pourrez ajouter des champs.
                    </div>
                    <button class="btn btn-success" disabled>Ajouter le champ</button>
                    <?php else : ?>
                    <button type="button"
                        class="btn btn-success"
                        onclick="Joomla.submitbutton('storage.addfield');">
                        <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_NEW_FIELD'); ?>
                    </button>
                        <table class="admintable" width="100%">
                            <tr>
                                <td>
                                    <label for="fieldname">
                                        <b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_NG_NAME'); ?>
                                        </b>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input class="form-control form-control-sm w-100" type="text" id="fieldname"
                                        name="jform[fieldname]" value="" />
                                </td>
                            </tr>
                            <tr>
                                <td width="100">
                                    <label for="fieldtitle">
                                        <b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_TITLE'); ?>
                                        </b>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input class="form-control form-control-sm w-100" type="text" id="fieldtitle"
                                        name="jform[fieldtitle]" value="" />
                                </td>
                            </tr>
                            <tr>
                                <td width="100">
                                    <label for="is_group">
                                        <b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_GROUP'); ?>
                                        </b>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input class="form-check-input" type="radio" id="is_group" name="jform[is_group]"
                                        value="1" /> <label for="is_group">
                                        <?php echo Text::_('COM_CONTENTBUILDER_NG_YES'); ?>
                                    </label>
                                    <input class="form-check-input" type="radio" id="is_group_no" name="jform[is_group]"
                                        value="0" checked="checked" /> <label for="is_group_no">
                                        <?php echo Text::_('COM_CONTENTBUILDER_NG_NO'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td width="100">
                                    <label for="group_definition">
                                        <b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_NG_STORAGE_GROUP_DEFINITION'); ?>
                                        </b>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">
                                    <textarea class="form-control form-control-sm" style="width: 100%; height: 100px;"
                                        id="group_definition" name="jform[group_definition]">Label 1;value1
    Label 2;value2
    Label 3;value3</textarea>
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>
                    </fieldset>
                <?php
                }
                ?>
            </td>

            <td valign="top">
                <table class="table table-striped m-3" style="min-width: 697px;">
                    <thead>
                        <tr>
                            <th width="5">
                                <a href="<?php echo htmlspecialchars((string) $sortLinks['id']['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDER_NG_ID'), ENT_QUOTES, 'UTF-8'); ?><?php echo $sortLinks['id']['indicator']; ?>
                                </a>
                            </th>
                            <th width="20">
                                <input class="form-check-input" type="checkbox" name="toggle" value=""
                                    onclick="Joomla.checkAll(this);" />
                            </th>
                            <th>
                                <a href="<?php echo htmlspecialchars((string) $sortLinks['name']['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDER_NG_NAME'), ENT_QUOTES, 'UTF-8'); ?><?php echo $sortLinks['name']['indicator']; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo htmlspecialchars((string) $sortLinks['title']['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDER_NG_STORAGE_TITLE'), ENT_QUOTES, 'UTF-8'); ?><?php echo $sortLinks['title']['indicator']; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo htmlspecialchars((string) $sortLinks['group_definition']['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDER_NG_STORAGE_GROUP'), ENT_QUOTES, 'UTF-8'); ?><?php echo $sortLinks['group_definition']['indicator']; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo htmlspecialchars((string) $sortLinks['ordering']['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDER_NG_ORDERBY'), ENT_QUOTES, 'UTF-8'); ?><?php echo $sortLinks['ordering']['indicator']; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo htmlspecialchars((string) $sortLinks['published']['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDER_NG_PUBLISHED'), ENT_QUOTES, 'UTF-8'); ?><?php echo $sortLinks['published']['indicator']; ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <?php
                    $fields = $this->fields ?? [];
                    $n      = is_countable($fields) ? count($fields) : 0;
                    ?>
                    <?php foreach ($fields as $i => $row) :
                        $id    = (int) ($row->id ?? 0);
                        $name  = htmlspecialchars((string) ($row->name ?? ''), ENT_QUOTES, 'UTF-8');
                        $title = htmlspecialchars((string) ($row->title ?? ''), ENT_QUOTES, 'UTF-8');
                        $group_definition = htmlspecialchars((string) ($row->group_definition ?? ''), ENT_QUOTES, 'UTF-8');
                        $isGroup = !empty($row->is_group);

                        $checked   = HTMLHelper::_('grid.id', $i, $id);

                        // ✅ RECO : passer en jgrid.published (tu l’as déjà validé côté listes)
                        // Important : le prefix "storage." doit matcher tes tasks côté controller
                        $published = HTMLHelper::_('jgrid.published', $row->published, $i, 'storage.', true);

                        // ordering: n’active les flèches que si ordering est vrai
                        $canOrder = !empty($this->ordering);
                    ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td><?php echo $id; ?></td>

                            <td class="text-center"><?php echo $checked; ?></td>
                            <td><?php echo $name; ?></td>
                            <td><?php echo $title; ?></td>
                            <td>
                                <input type="hidden" name="itemNames[<?php echo $id; ?>]" value="<?php echo $name; ?>" />
                                <input type="hidden" name="itemTitles[<?php echo $id; ?>]" value="<?php echo $title; ?>" />

                                <input class="form-check-input" type="radio"
                                    name="itemIsGroup[<?php echo $id; ?>]"
                                    value="1"
                                    id="itemIsGroup_<?php echo $id; ?>"
                                    <?php echo $isGroup ? 'checked="checked"' : ''; ?> />
                                <label for="itemIsGroup_<?php echo $id; ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_NG_YES'); ?>
                                </label>

                                <input class="form-check-input" type="radio"
                                    name="itemIsGroup[<?php echo $id; ?>]"
                                    value="0"
                                    id="itemIsGroupNo_<?php echo $id; ?>"
                                    <?php echo !$isGroup ? 'checked="checked"' : ''; ?> />
                                <label for="itemIsGroupNo_<?php echo $id; ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_NG_NO'); ?>
                                </label>

                                <div id="itemGroupDefinitions_<?php echo $id; ?>">
                                    <button type="button" class="btn btn-link btn-sm p-0"
                                        onclick="document.getElementById('itemGroupDefinitions<?php echo $id; ?>').style.display='block'; this.parentNode.style.display='none'; document.getElementById('itemGroupDefinitions<?php echo $id; ?>').focus(); return false;">
                                        [<?php echo Text::_('COM_CONTENTBUILDER_NG_EDIT'); ?>]
                                    </button>
                                </div>
                                <textarea class="form-control form-control-sm mt-1"
                                    onblur="this.style.display='none'; document.getElementById('itemGroupDefinitions_<?php echo $id; ?>').style.display='block';"
                                    id="itemGroupDefinitions<?php echo $id; ?>"
                                    style="display:none; width:100%; height:50px;"
                                    name="itemGroupDefinitions[<?php echo $id; ?>]"><?php echo $group_definition; ?></textarea>
                            </td>
                          
                            <td class="order text-nowrap">
                                <?php if ($canOrder) : ?>
                                    <span class="me-2">
                                        <?php echo $this->pagination->orderUpIcon($i, true, 'storage.orderup', 'JLIB_HTML_MOVE_UP', $this->ordering); ?>
                                    </span>
                                    <span>
                                        <?php echo $this->pagination->orderDownIcon($i, $n, true, 'storage.orderdown', 'JLIB_HTML_MOVE_DOWN', $this->ordering); ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center"><?php echo $published; ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <tfoot>
                        <tr>
                            <td colspan="11">
                                <div class="pagination pagination-toolbar">
                                    <div class="cbPagesCounter">
                                        <?php if (!empty($this->pagination)) {
                                            echo $this->pagination->getPagesCounter();
                                        } ?>
                                        <?php
                                        echo '<span>' . Text::_('COM_CONTENTBUILDER_NG_DISPLAY_NUM') . '&nbsp;</span>';
                                        echo '<div style="display:inline-block;">' . (empty($this->pagination) ? '' : $this->pagination->getLimitBox()) . '</div>';
                                        ?>
                                    </div>
                                    <?php if (!empty($this->pagination)) {
                                        echo $this->pagination->getPagesLinks();
                                    } ?>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <?php
    echo HTMLHelper::_('uitab.endTab');
    echo HTMLHelper::_('uitab.endTabSet');
    ?>

    <div class="clr">
    </div>

    <input type="hidden" name="option" value="com_contentbuilder_ng" />
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="task" value="storage.edit">
    <input type="hidden" name="jform[id]" value="<?php echo (int) $this->item->id; ?>" />
    <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
    <input type="hidden" name="jform[published]" value="<?php echo $this->item->published; ?>" />
    <input type="hidden" name="filter_order" value="<?php echo htmlspecialchars($listOrder, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars($listDirn, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="list[ordering]" value="<?php echo htmlspecialchars($listOrder, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="list[direction]" value="<?php echo htmlspecialchars($listDirn, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="list[start]" value="<?php echo (int) $this->state?->get('list.start', 0); ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="tabStartOffset" value="<?php echo Factory::getApplication()->getSession()->get('tabStartOffset', 0); ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
