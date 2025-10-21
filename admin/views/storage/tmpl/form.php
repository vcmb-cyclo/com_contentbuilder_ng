<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL 
 * @license     GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$___tableOrdering = "function tableOrdering";
$___tableOrdering = "Joomla.tableOrdering = function";
?>
<style type="text/css">
    .cbPagesCounter {
        float: left;
        padding-right: 10px;
        padding-top: 4px;
    }
</style>
<script language="javascript" type="text/javascript">
<!--
<?php echo $___tableOrdering; ?>(order, dir, task) {
        var form = document.adminForm;
        form.limitstart.value = <?php echo CBRequest::getInt('limitstart', 0) ?>;
        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit(task);
    };
    //-->
</script>
<script type="text/javascript">
    function saveorder(n, task) {
        checkAll_button(n, 'listsaveorder');
    }

    function listItemTask(id, task) {

        var f = document.adminForm;
        f.limitstart.value = <?php echo CBRequest::getInt('limitstart', 0) ?>;
        cb = eval('f.' + id);

        if (cb) {
            for (i = 0; true; i++) {
                cbx = eval('f.cb' + i);
                if (!cbx) break;
                cbx.checked = false;
            } // for
            cb.checked = true;
            f.boxchecked.value = 1;
            switch (task) {
                case 'delete':
                    task = 'listdelete';
                    break;
                case 'publish':
                    task = 'listpublish';
                    break;
                case 'unpublish':
                    task = 'listunpublish';
                    break;
                case 'orderdown':
                    task = 'listorderdown';
                    break;
                case 'orderup':
                    task = 'listorderup';
                    break;
            }

            submitbutton(task);
        }
        return false;
    }

    function submitbutton(pressbutton) {
        if (pressbutton == 'delete') {
            pressbutton = 'listdelete';
        }

        if (pressbutton == 'listdelete') {
            var result = confirm("<?php echo addslashes(Text::_('COM_CONTENTBUILDER_STORAGE_DELETE_WARNING')); ?>");
            if (!result) {
                return;
            }
        }

        switch (pressbutton) {
            case 'cancel':
            case 'listdelete':
            case 'listpublish':
            case 'listunpublish':
            case 'listorderdown':
            case 'listorderup':
                Joomla.submitform(pressbutton);
                break;
            case 'save':
            case 'saveNew':
            case 'apply':
                var error = false;
                var nodes = document.adminForm['cid[]'];

                if (document.getElementById('bytable').selectedIndex == 0) {

                    if (document.getElementById('name').value == '') {
                        error = true;
                        alert("<?php echo addslashes(Text::_('COM_CONTENTBUILDER_ERROR_ENTER_STORAGENAME')); ?>");
                    }
                    else if (nodes) {
                        if (typeof nodes.value != 'undefined') {
                            if (nodes.checked && document.adminForm['itemNames[' + nodes.value + ']'].value == '') {
                                error = true;
                                alert("<?php echo addslashes(Text::_('COM_CONTENTBUILDER_ERROR_ENTER_STORAGENAME')); ?>");
                                break;
                            }
                        }
                        else {
                            for (var i = 0; i < nodes.length; i++) {
                                if (nodes[i].checked && document.adminForm['itemNames[' + nodes[i].value + ']'].value == '') {
                                    error = true;
                                    alert("<?php echo addslashes(Text::_('COM_CONTENTBUILDER_ERROR_ENTER_STORAGENAME')); ?>");
                                    break;
                                }
                            }
                        }
                    }

                }

                if (!error) {
                    Joomla.submitform(pressbutton);
                }

                break;
        }
    }

    String.prototype.startsWith = function (str) {
        return (this.indexOf(str) === 0);
    }

    String.prototype.endsWith = function (suffix) {
        return this.indexOf(suffix, this.length - suffix.length) !== -1;
    };

    if (typeof Joomla != 'undefined') {
        Joomla.submitbutton = submitbutton;
        Joomla.listItemTask = listItemTask;
    }
</script>
<style type="text/css">
    label {
        display: inline;
    }
</style>
<?php
$cbcompat = new CBCompat();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
    <div class="col100" style="margin-left: 20px;overflow-x: auto;">

        <?php
        echo $cbcompat->startPane("view-pane");
        echo $cbcompat->startPanel(Text::_('COM_CONTENTBUILDER_STORAGE'), "tab0");
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
                                            <?php echo Text::_('COM_CONTENTBUILDER_NAME'); ?>
                                        </b>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php
                                    if (!$this->form->bytable) {
                                        ?>
                                        <input class="form-control form-control-sm w-100" type="text" id="name" name="name"
                                            value="<?php echo htmlentities($this->form->name ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                        <br /><br />
                                        <?php
                                    } else {
                                        ?>
                                        <input type="hidden" id="name" name="name"
                                            value="<?php echo htmlentities($this->form->name, ENT_QUOTES, 'UTF-8'); ?>" />
                                        <?php
                                    }

                                    if (!$this->form->id) {
                                        ?>
                                        <b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_CHOOSE_TABLE'); ?>
                                        </b>
                                        <br />
                                        <select class="form-select-sm"
                                            onchange="if(this.selectedIndex != 0){ document.getElementById('name').disabled = true; document.getElementById('csvUploadHead').style.display = 'none'; document.getElementById('csvUpload').style.display = 'none'; alert('<?php echo addslashes(Text::_('COM_CONTENTBUILDER_CUSTOM_STORAGE_MSG')); ?>'); }else{ document.getElementById('name').disabled = false; document.getElementById('csvUploadHead').style.display = ''; document.getElementById('csvUpload').style.display = ''; }"
                                            name="bytable" id="bytable" style="max-width: 150px;">
                                            <option value=""> -
                                                <?php echo Text::_('COM_CONTENTBUILDER_NONE'); ?> -
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
                                    } else if ($this->form->bytable) {
                                        ?>
                                            <input type="hidden" id="bytable" name="bytable"
                                                value="<?php echo htmlentities($this->form->name, ENT_QUOTES, 'UTF-8'); ?>" />
                                        <?php echo htmlentities($this->form->name, ENT_QUOTES, 'UTF-8'); ?>
                                        <?php
                                    } else if (!$this->form->bytable) {
                                        ?>
                                                <input type="hidden" id="bytable" name="bytable" value="" />
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td width="100">
                                    <label for="title">
                                        <b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_TITLE'); ?>
                                        </b>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input class="form-control form-control-sm w-100" type="text" id="title"
                                        name="title"
                                        value="<?php echo htmlentities($this->form->title ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </td>
                            </tr>
                            <tr id="csvUploadHead">
                                <td width="100">
                                    <br />
                                    <div class="mb-3"
                                        onclick="if(document.getElementById('csvUpload').style.display == 'none'){document.getElementById('csvUpload').style.display='';}else{document.getElementById('csvUpload').style.display='none'}"
                                        style="cursor:pointer;">
                                        <b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_UPDATE_FROM_CSV'); ?>
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
                                        <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_UPDATE_FROM_CSV_DROP_RECORDS'); ?>
                                    </label> <input class="form-check-input" type="checkbox" id="csv_drop_records"
                                        name="csv_drop_records" value="1" checked="checked" />
                                    <br />
                                    <label for="csv_published">
                                        <?php echo Text::_('COM_CONTENTBUILDER_AUTO_PUBLISH'); ?>
                                    </label> <input class="form-check-input" type="checkbox" id="csv_published"
                                        name="csv_published" value="1" checked="checked" />
                                    <br />
                                    <label for="csv_delimiter">
                                        <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_UPDATE_FROM_CSV_DELIMITER'); ?>
                                    </label> <input class="form-control form-control-sm" maxlength="3" type="text"
                                        size="1" id="csv_delimiter" name="csv_delimiter" value="," />
                                    <br />
                                    <br />
                                    <label class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_STORAGE_UPDATE_FROM_CSV_REPAIR_ENCODING_TIP'); ?>"
                                        for="csv_repair_encoding">
                                        <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_UPDATE_FROM_CSV_REPAIR_ENCODING'); ?>*
                                    </label>
                                    <br />
                                    <select class="form-select-sm" style="width: 150px;" name="csv_repair_encoding"
                                        id="csv_repair_encoding">
                                        <option value=""> -
                                            <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_UPDATE_FROM_CSV_NO_REPAIR_ENCODING'); ?>
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
                    if (!$this->form->bytable) {
                        ?>
                        <fieldset class="adminform">
                            <legend>
                                <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_NEW_FIELD'); ?>
                            </legend>
                            <table class="admintable" width="100%">
                                <tr>
                                    <td>
                                        <label for="fieldname">
                                            <b>
                                                <?php echo Text::_('COM_CONTENTBUILDER_NAME'); ?>
                                            </b>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input class="form-control form-control-sm w-100" type="text" id="fieldname"
                                            name="fieldname" value="" />
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100">
                                        <label for="fieldtitle">
                                            <b>
                                                <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_TITLE'); ?>
                                            </b>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input class="form-control form-control-sm w-100" type="text" id="fieldtitle"
                                            name="fieldtitle" value="" />
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100">
                                        <label for="is_group">
                                            <b>
                                                <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_GROUP'); ?>
                                            </b>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input class="form-check-input" type="radio" id="is_group" name="is_group"
                                            value="1" /> <label for="is_group">
                                            <?php echo Text::_('COM_CONTENTBUILDER_YES'); ?>
                                        </label>
                                        <input class="form-check-input" type="radio" id="is_group_no" name="is_group"
                                            value="0" checked="checked" /> <label for="is_group_no">
                                            <?php echo Text::_('COM_CONTENTBUILDER_NO'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100">
                                        <label for="group_definition">
                                            <b>
                                                <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_GROUP_DEFINITION'); ?>
                                            </b>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">
                                        <textarea class="form-control form-control-sm" style="width: 100%; height: 100px;"
                                            id="group_definition" name="group_definition">Label 1;value1
    Label 2;value2
    Label 3;value3</textarea>
                                    </td>
                                </tr>
                            </table>
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
                                    <?php echo Text::_('COM_CONTENTBUILDER_ID'); ?>
                                </th>
                                <th width="20">
                                    <input class="form-check-input" type="checkbox" name="toggle" value=""
                                        onclick="Joomla.checkAll(this);" />
                                </th>
                                <th>
                                    <?php echo Text::_('COM_CONTENTBUILDER_NAME'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_TITLE'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('COM_CONTENTBUILDER_STORAGE_GROUP'); ?>
                                </th>
                                <th>
                                    <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_ORDERBY'), 'ordering', 'desc', @$this->lists['order'], 'edit'); ?>
                                    <?php // TODO: draganddrop if ($this->ordering) echo HTMLHelper::_('grid.order',  $this->elements );  ?>
                                </th>
                                <th>
                                    <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED'); ?>
                                </th>
                            </tr>
                        </thead>
                        <?php
                        $k = 0;
                        $n = count($this->elements);
                        for ($i = 0; $i < $n; $i++) {
                            $row = $this->elements[$i];
                            $checked = HTMLHelper::_('grid.id', $i, $row->id);
                            $published = contentbuilder_helpers::listPublish($row, $i);
                            ?>
                            <tr class="<?php echo "row$k"; ?>">
                                <td>
                                    <?php echo $row->id; ?>
                                </td>
                                <td align="center">
                                    <?php echo $checked; ?>
                                </td>
                                <td width="100">
                                    <div style="cursor:pointer;width: 100%;display:block;"
                                        id="itemNames_<?php echo $row->id ?>"
                                        onclick="if(<?php echo $this->form->bytable ? 'true' : 'false'; ?>){ return; }document.getElementById('itemNames<?php echo $row->id ?>').style.display='block';this.style.display='none';document.getElementById('itemNames<?php echo $row->id ?>').focus();">
                                        <?php echo htmlentities($row->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <input class="form-control form-control-sm w-100"
                                        onblur="if(this.value=='') {this.value = 'Unnamed';} this.style.display='none';document.getElementById('itemNames_<?php echo $row->id ?>').innerHTML=this.value;document.getElementById('itemNames_<?php echo $row->id ?>').style.display='block';"
                                        id="itemNames<?php echo $row->id ?>" type="text" style="display:none; width: 100%;"
                                        name="itemNames[<?php echo $row->id ?>]"
                                        value="<?php echo htmlentities($row->name, ENT_QUOTES, 'UTF-8') ?>" />
                                </td>
                                <td width="100">
                                    <div style="cursor:pointer;width: 100%;display:block;"
                                        id="itemTitles_<?php echo $row->id ?>"
                                        onclick="document.getElementById('itemTitles<?php echo $row->id ?>').style.display='block';this.style.display='none';document.getElementById('itemTitles<?php echo $row->id ?>').focus();">
                                        <?php echo htmlentities($row->title, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <input class="form-control form-control-sm w-100"
                                        onblur="if(this.value=='') {this.value = 'Untitled';} this.style.display='none';document.getElementById('itemTitles_<?php echo $row->id ?>').innerHTML=this.value;document.getElementById('itemTitles_<?php echo $row->id ?>').style.display='block';"
                                        id="itemTitles<?php echo $row->id ?>" type="text" style="display:none; width: 100%;"
                                        name="itemTitles[<?php echo $row->id ?>]"
                                        value="<?php echo htmlentities($row->title, ENT_QUOTES, 'UTF-8') ?>" />
                                </td>
                                <td width="200" align="center">
                                    <input class="form-check-input" type="radio" name="itemIsGroup[<?php echo $row->id ?>]"
                                        value="1" id="itemIsGroup_<?php echo $row->id ?>" <?php echo $row->is_group ? ' checked="checked"' : '' ?> /> <label for="itemIsGroup_<?php echo $row->id ?>">
                                        <?php echo Text::_('COM_CONTENTBUILDER_YES'); ?>
                                    </label>
                                    <input class="form-check-input" type="radio" name="itemIsGroup[<?php echo $row->id ?>]"
                                        value="0" id="itemIsGroupNo_<?php echo $row->id ?>" <?php echo !$row->is_group ? ' checked="checked"' : '' ?> /> <label for="itemIsGroupNo_<?php echo $row->id ?>" />
                                    <?php echo Text::_('COM_CONTENTBUILDER_NO'); ?></label>
                                    <div style="cursor:pointer;width: 100%;display:block;"
                                        id="itemGroupDefinitions_<?php echo $row->id ?>"
                                        onclick="document.getElementById('itemGroupDefinitions<?php echo $row->id ?>').style.display='block';this.style.display='none';document.getElementById('itemGroupDefinitions<?php echo $row->id ?>').focus();">
                                        <?php echo htmlentities('[' . Text::_('COM_CONTENTBUILDER_EDIT') . ']', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <textarea class="form-control form-control-sm"
                                        onblur="if(this.value=='') {this.value = '';} this.style.display='none';document.getElementById('itemGroupDefinitions_<?php echo $row->id ?>').style.display='block';"
                                        id="itemGroupDefinitions<?php echo $row->id ?>"
                                        style="display:none; width: 100%;height:50px;"
                                        name="itemGroupDefinitions[<?php echo $row->id ?>]"><?php echo htmlentities($row->group_definition, ENT_QUOTES, 'UTF-8') ?></textarea>
                                </td>
                                <td class="order" nowrap="nowrap" width="100">
                                    <span>
                                        <?php echo $this->pagination->orderUpIcon($i, true, 'orderup', 'Move Up', $this->ordering); ?>
                                    </span>
                                    <span>
                                        <?php echo $this->pagination->orderDownIcon($i, $n, true, 'orderdown', 'Move Down', $this->ordering); ?>
                                    </span>
                                    <?php $disabled = $this->ordering ? '' : 'disabled="disabled"'; ?>

                                </td>
                                <td width="25">
                                    <?php echo $published; ?>
                                </td>
                            </tr>
                            <?php
                            $k = 1 - $k;
                        }
                        ?>
                        <tfoot>
                            <tr>
                                <td colspan="11">
                                    <div class="pagination pagination-toolbar">
                                        <div class="cbPagesCounter">
                                            <?php echo $this->pagination->getPagesCounter(); ?>
                                            <?php
                                            echo '<span>' . Text::_('COM_CONTENTBUILDER_DISPLAY_NUM') . '&nbsp;</span>';
                                            echo '<div style="display:inline-block;">' . $this->pagination->getLimitBox() . '</div>';
                                            ?>
                                        </div>
                                        <?php echo $this->pagination->getPagesLinks(); ?>
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

    </div>

    <div class="clr"></div>

    <input type="hidden" name="option" value="com_contentbuilder" />
    <input type="hidden" name="id" value="<?php echo $this->form->id; ?>" />
    <input type="hidden" name="task" value="edit" />
    <input type="hidden" name="controller" value="storages" />
    <input type="hidden" name="limitstart" value="" />
    <input type="hidden" name="ordering" value="<?php echo $this->form->ordering; ?>" />
    <input type="hidden" name="published" value="<?php echo $this->form->published; ?>" />
    <input type="hidden" name="filter_order" value="" />
    <input type="hidden" name="filter_order_Dir" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="tabStartOffset" value="<?php echo Factory::getApplication()->getSession()->get('tabStartOffset', 0); ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script type="text/javascript">

    if (typeof Joomla != 'undefined') {
        $$('.tab0').addEvent('click', function () { document.adminForm.tabStartOffset.value = 0; });
    } else {
        $('tab0').addEvent('click', function () { document.adminForm.tabStartOffset.value = 0; });
    }
</script>
