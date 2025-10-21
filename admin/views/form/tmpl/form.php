<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL 
 * @license     GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$___tableOrdering = "Joomla.tableOrdering = function";

?>
<style type="text/css">
    .cbPagesCounter {
        float: left;
        padding-right: 10px;
        padding-top: 4px;
    }
</style>
<script type="text/javascript">
    function saveorder(n, task) {
        console.log('saveorder called with n=', n, 'task=', task);
        Joomla.checkAll(n, 'listsaveorder');

        var form = document.adminForm;
        
        // Ensure the task is set correctly
        // form.task.value = task || 'saveorder';
        

        // Submit the form using Joomla's submitform
        // Joomla.submitform(form.task.value);
    }

    <?php echo $___tableOrdering; ?>(order, dir, task) {
        var form = document.adminForm;
        form.limitstart.value = <?php echo CBRequest::getInt('limitstart', 0) ?>;
        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit(task);
    };

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
        if (pressbutton == 'remove') {
            pressbutton = 'listremove';
        }

        switch (pressbutton) {
            case 'cancel':
            case 'listpublish':
            case 'listunpublish':
            case 'listorderdown':
            case 'listorderup':
            case 'listremove':
            case 'list_include':
            case 'no_list_include':
            case 'search_include':
            case 'no_search_include':
            case 'linkable':
            case 'not_linkable':
            case 'editable':
            case 'not_editable':
                Joomla.submitform(pressbutton);
                break;
            case 'save':
            case 'saveNew':
            case 'apply':
                var error = false;
                var nodes = document.adminForm['cid[]'];

                if (document.getElementById('name').value == '') {
                    error = true;
                    alert("<?php echo addslashes(Text::_('COM_CONTENTBUILDER_ERROR_ENTER_FORMNAME')); ?>");
                }
                else if (nodes) {
                    if (typeof nodes.value != 'undefined') {
                        if (nodes.checked && document.adminForm['elementLabels[' + nodes.value + ']'].value == '') {
                            error = true;
                            alert("<?php echo addslashes(Text::_('COM_CONTENTBUILDER_ERROR_ENTER_FORMNAME_ALL')); ?>");
                            break;
                        }
                    }
                    else {
                        for (var i = 0; i < nodes.length; i++) {
                            if (nodes[i].checked && document.adminForm['elementLabels[' + nodes[i].value + ']'].value == '') {
                                error = true;
                                alert("<?php echo addslashes(Text::_('COM_CONTENTBUILDER_ERROR_ENTER_FORMNAME_ALL')); ?>");
                                break;
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

    if (typeof Joomla != 'undefined') {
        Joomla.submitbutton = submitbutton;
        Joomla.listItemTask = listItemTask;
    }

    String.prototype.startsWith = function (str) {
        return (this.indexOf(str) === 0);
    }

    String.prototype.endsWith = function (suffix) {
        return this.indexOf(suffix, this.length - suffix.length) !== -1;
    };

    function contentbuilder_selectAll(checker, type) {
        var type = type == 'fe' ? 'perms_fe[' : 'perms[';
        for (var i = 0; i < document.adminForm.elements.length; i++) {
            if (typeof document.adminForm.elements[i].name != 'undefined' && document.adminForm.elements[i].name.startsWith(type) && document.adminForm.elements[i].name.endsWith(checker.value + "]")) {
                if (checker.checked) {
                    document.adminForm.elements[i].checked = true;
                } else {
                    document.adminForm.elements[i].checked = false;
                }
            }
        }
    }
</script>
<?php
$cbcompat = new CBCompat();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="col100 row-fluid" style="margin-left: 20px; overflow-x: auto;">

        <?php
        echo $cbcompat->startPane("view-pane");
        echo $cbcompat->startPanel(Text::_('COM_CONTENTBUILDER_VIEW'), "tab0");
        ?>

        <table width="100%">
            <tr>
                <td valign="top">

                    <fieldset class="adminform">

                        <label for="name">
                            <span class="editlinktip hasTip"
                                title="<?php echo Text::_('COM_CONTENTBUILDER_VIEW_NAME_TIP'); ?>"><b>
                                    <?php echo Text::_('COM_CONTENTBUILDER_NAME'); ?>:
                                </b></span>
                        </label>
                        <input class="form-control form-control-sm" type="text" name="name" id="name" size="32"
                            style="width: 200px;" maxlength="255"
                            value="<?php echo htmlentities($this->form->name ?? '', ENT_QUOTES, 'UTF-8'); ?>" />

                        <label for="tag">
                            <span class="editlinktip hasTip"
                                title="<?php echo Text::_('COM_CONTENTBUILDER_VIEW_TAG_TIP'); ?>"><b>
                                    <?php echo Text::_('COM_CONTENTBUILDER_TAG'); ?>:
                                </b></span>
                        </label>
                        <input class="form-control form-control-sm" type="text" name="tag" id="tag" size="32"
                            style="width: 200px;" maxlength="255"
                            value="<?php echo htmlentities($this->form->tag ?? '', ENT_QUOTES, 'UTF-8'); ?>" />

                        <?php
                        if ($this->form->id < 1) {
                            ?>
                            <label for="types">
                                <span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_TYPE_TIP'); ?>"><b>
                                        <?php echo Text::_('COM_CONTENTBUILDER_TYPE'); ?>:
                                    </b></span>
                            </label>
                            <select class="form-select-sm" name="type">
                                <?php
                                foreach ($this->form->types as $type) {
                                    if (trim($type)) {
                                        ?>
                                        <option value="<?php echo $type ?>">
                                            <?php echo $type ?>
                                        </option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>

                            <button type="button" class="btn-primary btn-sm"
                                onclick="if(document.getElementById('advancedOptions').style.display == 'none'){document.getElementById('advancedOptions').style.display = '';}else{document.getElementById('advancedOptions').style.display = 'none';}">
                                <?php echo Text::_('COM_CONTENTBUILDER_ADVANCED_OPTIONS'); ?>
                            </button>

                            <?php
                        } else {
                            ?>
                            <button type="button" class="btn-sm btn-primary"
                                onclick="if(document.getElementById('advancedOptions').style.display == 'none'){document.getElementById('advancedOptions').style.display = '';}else{document.getElementById('advancedOptions').style.display = 'none';}">
                                <?php echo Text::_('COM_CONTENTBUILDER_ADVANCED_OPTIONS'); ?>
                            </button>

                            <div></div>

                            <div class="alert">

                                <label for="name">
                                    <b>
                                        <?php echo Text::_('COM_CONTENTBUILDER_FORM_SOURCE'); ?>:
                                    </b>
                                </label>
                                <?php

                                if (!$this->form->reference_id) {
                                    ?>
                                    <select class="form-select-sm" name="reference_id" style="max-width: 200px;">
                                        <?php
                                        foreach ($this->form->forms as $reference_id => $title) {
                                            ?>
                                            <option value="<?php echo $reference_id ?>">
                                                <?php echo htmlentities($title ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <?php
                                } else {
                                    ?>
                                    <?php echo htmlentities($this->form->form->getTitle() ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    <input type="hidden" name="reference_id"
                                        value="<?php echo $this->form->form->getReferenceId(); ?>" />
                                    <?php
                                }
                                ?>

                                <label for="types">
                                    <span class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_TYPE_TIP'); ?>"><b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_TYPE'); ?>:
                                        </b></span>
                                </label>
                                <?php echo $this->form->type ?>
                                <input type="hidden" name="type" value="<?php echo $this->form->type ?>" />
                                <input type="hidden" name="type_name"
                                    value="<?php echo isset($this->form->type_name) ? $this->form->type_name : ''; ?>" />
                                <?php
                                if ($this->form->type != 'com_contentbuilder') {
                                    ?>
                                    <input class="form-check-input" type="checkbox" id="edit_by_type" name="edit_by_type"
                                        value="1" <?php echo $this->form->edit_by_type ? ' checked="checked"' : '' ?> />
                                    <label for="edit_by_type">
                                        <?php echo Text::_('COM_CONTENTBUILDER_TYPE_EDIT'); ?>
                                    </label>
                                    <?php
                                } else {
                                    ?>
                                    <input type="hidden" name="edit_by_type" value="0" />
                                    <?php
                                }
                                ?>
                                <input class="form-check-input" type="checkbox" id="email_notifications"
                                    name="email_notifications" value="1" <?php echo $this->form->email_notifications ? ' checked="checked"' : '' ?> />
                                <label for="email_notifications">
                                    <?php echo Text::_('COM_CONTENTBUILDER_TYPE_EMAIL_NOTIFICATIONS'); ?>
                                </label>
                                <input class="form-check-input" type="checkbox" id="email_update_notifications"
                                    name="email_update_notifications" value="1" <?php echo $this->form->email_update_notifications ? ' checked="checked"' : '' ?> />
                                <label for="email_update_notifications">
                                    <?php echo Text::_('COM_CONTENTBUILDER_TYPE_EMAIL_UPDATE_NOTIFICATIONS'); ?>
                                </label>

                            </div>

                            <div></div>

                            <?php
                        }
                        ?>

                        <div class="bg-light p-3" style="display: none;" id="advancedOptions">

                            <fieldset>
                                <legend>
                                    <h3 class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_DISPLAY_TIP'); ?>">
                                        <?php echo Text::_('COM_CONTENTBUILDER_DISPLAY'); ?>
                                    </h3>
                                </legend>


                                <select class="form-select-sm" name="display_in">
                                    <option value="0" <?php echo $this->form->display_in == 0 ? ' selected="selected"' : '' ?>>
                                        <?php echo Text::_('COM_CONTENTBUILDER_DISPLAY_FRONTEND') ?>
                                    </option>
                                    <option value="1" <?php echo $this->form->display_in == 1 ? ' selected="selected"' : '' ?>>
                                        <?php echo Text::_('COM_CONTENTBUILDER_DISPLAY_BACKEND') ?>
                                    </option>
                                    <option value="2" <?php echo $this->form->display_in == 2 ? ' selected="selected"' : '' ?>>
                                        <?php echo Text::_('COM_CONTENTBUILDER_DISPLAY_BOTH') ?>
                                    </option>
                                </select>

                                <label for="theme_plugin">
                                    <span class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_THEME_PLUGIN_TIP'); ?>"><b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_THEME_PLUGIN'); ?>:
                                        </b></span>
                                </label>
                                <select class="form-select-sm" name="theme_plugin" id="theme_plugin">
                                    <?php
                                    foreach ($this->theme_plugins as $theme_plugin) {
                                        ?>
                                        <option value="<?php echo $theme_plugin; ?>" <?php echo $theme_plugin == $this->form->theme_plugin ? ' selected="selected"' : ''; ?>>
                                            <?php echo $theme_plugin; ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>

                            </fieldset>

                            <hr />

                            <fieldset>
                                <legend>
                                    <h3 class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_SHOW_COLUMNS_TIP'); ?>">
                                        <?php echo Text::_('COM_CONTENTBUILDER_SHOW'); ?>
                                    </h3>
                                </legend>


                                <input class="form-check-input" type="checkbox" id="show_id_column"
                                    name="show_id_column" value="1" <?php echo $this->form->show_id_column ? ' checked="checked"' : '' ?> /> <label for="show_id_column">
                                    <?php echo Text::_('COM_CONTENTBUILDER_ID_COLUMN'); ?>
                                </label>
                                <input class="form-check-input" type="checkbox" id="select_column" name="select_column"
                                    value="1" <?php echo $this->form->select_column ? ' checked="checked"' : '' ?> />
                                <label for="select_column">
                                    <?php echo Text::_('COM_CONTENTBUILDER_SELECT_COLUMN'); ?>
                                </label>
                                <input class="form-check-input" type="checkbox" id="list_state" name="list_state"
                                    value="1" <?php echo $this->form->list_state ? ' checked="checked"' : '' ?> />
                                <label for="list_state">
                                    <?php echo Text::_('COM_CONTENTBUILDER_EDIT_STATE'); ?>
                                </label>
                                <input class="form-check-input" type="checkbox" id="edit_button" name="edit_button"
                                    value="1" <?php echo $this->form->edit_button ? ' checked="checked"' : '' ?> />
                                <label for="edit_button">
                                    <?php echo Text::_('COM_CONTENTBUILDER_EDIT_BUTTON'); ?>
                                </label>
                                <input class="form-check-input" type="checkbox" id="list_publish" name="list_publish"
                                    value="1" <?php echo $this->form->list_publish ? ' checked="checked"' : '' ?> />
                                <label for="list_publish">
                                    <?php echo Text::_('PUBLISH'); ?>
                                </label>
                                <input class="form-check-input" type="checkbox" id="list_language" name="list_language"
                                    value="1" <?php echo $this->form->list_language ? ' checked="checked"' : '' ?> />
                                <label for="list_language">
                                    <?php echo Text::_('COM_CONTENTBUILDER_LANGUAGE'); ?>
                                </label>
                                <input class="form-check-input" type="checkbox" id="list_article" name="list_article"
                                    value="1" <?php echo $this->form->list_article ? ' checked="checked"' : '' ?> />
                                <label for="list_article">
                                    <?php echo Text::_('COM_CONTENTBUILDER_ARTICLE'); ?>
                                </label>
                                <input class="form-check-input" type="checkbox" id="list_author" name="list_author"
                                    value="1" <?php echo $this->form->list_author ? ' checked="checked"' : '' ?> />
                                <label for="list_author">
                                    <?php echo Text::_('COM_CONTENTBUILDER_AUTHOR'); ?>
                                </label>



                                <input class="form-check-input" type="checkbox" name="export_xls" id="export_xls"
                                    value="1" <?php echo $this->form->export_xls ? ' checked="checked"' : '' ?> />
                                <label for="export_xls">
                                    <span class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_SHOW_XLSEXPORT_TIP'); ?>">
                                        <?php echo Text::_('COM_CONTENTBUILDER_SHOW_XLSEXPORT'); ?>
                                    </span>
                                </label>

                                <input class="form-check-input" type="checkbox" name="print_button" id="print_button"
                                    value="1" <?php echo $this->form->print_button ? ' checked="checked"' : '' ?> />
                                <label for="print_button">
                                    <span class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_SHOW_PRINTBUTTON_TIP'); ?>">
                                        <?php echo Text::_('COM_CONTENTBUILDER_SHOW_PRINTBUTTON'); ?>
                                    </span>
                                </label>

                                <input class="form-check-input" type="checkbox" name="metadata" id="metadata" value="1"
                                    <?php echo $this->form->metadata ? ' checked="checked"' : '' ?> />
                                <label for="metadata">
                                    <span class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_SHOW_METADATA_TIP'); ?>">
                                        <?php echo Text::_('COM_CONTENTBUILDER_SHOW_METADATA'); ?>
                                    </span>
                                </label>

                                <input class="form-check-input" type="checkbox" name="show_filter" id="show_filter"
                                    value="1" <?php echo $this->form->show_filter ? ' checked="checked"' : '' ?> />
                                <label for="show_filter">
                                    <span class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_SHOW_FILTER_TIP'); ?>">
                                        <?php echo Text::_('COM_CONTENTBUILDER_SHOW_FILTER'); ?>
                                    </span>
                                </label>

                                <input class="form-check-input" type="checkbox" name="show_records_per_page"
                                    id="show_records_per_page" value="1" <?php echo $this->form->show_records_per_page ? ' checked="checked"' : '' ?> />
                                <label for="show_records_per_page">
                                    <span class="editlinktip hasTip"
                                        title="<?php echo Text::_('COM_CONTENTBUILDER_SHOW_RECORDS_PER_PAGE_TIP'); ?>">
                                        <?php echo Text::_('COM_CONTENTBUILDER_SHOW_RECORDS_PER_PAGE'); ?>
                                    </span>
                                </label>

                            </fieldset>

                            <hr />

                            <fieldset>
                                <legend>
                                    <h3>
                                        <?php echo Text::_('COM_CONTENTBUILDER_RATING'); ?>
                                    </h3>
                                </legend>
                                <div class="alert">
                                    <input class="form-check-input" type="checkbox" id="list_rating" name="list_rating"
                                        value="1" <?php echo $this->form->list_rating ? ' checked="checked"' : '' ?> />
                                    <label for="list_rating">
                                        <?php echo Text::_('COM_CONTENTBUILDER_RATING'); ?>
                                    </label>

                                    <select class="form-select-sm" name="rating_slots" id="rating_slots">
                                        <option value="1" <?php echo $this->form->rating_slots == 1 ? ' selected="selected"' : ''; ?>>1</option>
                                        <option value="2" <?php echo $this->form->rating_slots == 2 ? ' selected="selected"' : ''; ?>>2</option>
                                        <option value="3" <?php echo $this->form->rating_slots == 3 ? ' selected="selected"' : ''; ?>>3</option>
                                        <option value="4" <?php echo $this->form->rating_slots == 4 ? ' selected="selected"' : ''; ?>>4</option>
                                        <option value="5" <?php echo $this->form->rating_slots == 5 ? ' selected="selected"' : ''; ?>>5</option>
                                    </select>
                                    <label for="rating_slots">
                                        <?php echo Text::_('COM_CONTENTBUILDER_RATING_SLOTS'); ?>
                                    </label>
                                </div>
                            </fieldset>

                            <hr />

                            <fieldset>
                                <legend>
                                    <h3>
                                        <?php echo Text::_('COM_CONTENTBUILDER_SORTING'); ?>
                                    </h3>
                                </legend>
                                <div class="alert">
                                    <label for="initial_sort_order">
                                        <span class="editlinktip hasTip"
                                            title="<?php echo Text::_('COM_CONTENTBUILDER_INITIAL_SORT_ORDER_TIP'); ?>"><b>
                                                <?php echo Text::_('COM_CONTENTBUILDER_INITIAL_SORT_ORDER'); ?>:
                                            </b></span>
                                    </label>
                                    <select class="form-select-sm"
                                        onchange="if(this.selectedIndex == 3) { document.getElementById('randUpdate').style.display='block'; } else { document.getElementById('randUpdate').style.display='none'; } "
                                        name="initial_sort_order" id="initial_sort_order" style="max-width: 200px;">
                                        <option value="-1">
                                            <?php echo Text::_('COM_CONTENTBUILDER_INITIAL_SORT_ORDER_BY_ID'); ?>
                                        </option>
                                        <option value="Rating" <?php echo $this->form->initial_sort_order == 'Rating' ? ' selected="selected"' : ''; ?>>
                                            <?php echo Text::_('COM_CONTENTBUILDER_RATING'); ?>
                                        </option>
                                        <option value="RatingCount" <?php echo $this->form->initial_sort_order == 'RatingCount' ? ' selected="selected"' : ''; ?>>
                                            <?php echo Text::_('COM_CONTENTBUILDER_RATING_COUNT'); ?>
                                        </option>
                                        <option value="Rand" <?php echo $this->form->initial_sort_order == 'Rand' ? ' selected="selected"' : ''; ?>>
                                            <?php echo Text::_('COM_CONTENTBUILDER_INITIAL_SORT_ORDER_RAND'); ?>
                                        </option>
                                        <?php
                                        foreach ($this->elements as $sortable) {
                                            ?>
                                            <option value="<?php echo $sortable->reference_id; ?>" <?php echo $this->form->initial_sort_order == $sortable->reference_id ? ' selected="selected"' : ''; ?>>
                                                <?php echo htmlentities($sortable->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </value>
                                                <?php
                                        }
                                        ?>
                                    </select>
                                    <span id="randUpdate"
                                        style="display: <?php echo $this->form->initial_sort_order == 'Rand' ? 'block' : 'none' ?>;">
                                        <b>
                                            <?php echo Text::_('COM_CONTENTBUILDER_RAND_UPDATE'); ?>:
                                        </b>
                                        <input class="form-control form-control-sm" type="text" name="rand_update"
                                            value="<?php echo $this->form->rand_update; ?>" />
                                    </span>
                                    <select class="form-select-sm" name="initial_sort_order2" id="initial_sort_order2"
                                        style="max-width: 200px;">
                                        <option value="-1">
                                            <?php echo Text::_('COM_CONTENTBUILDER_NONE'); ?>
                                        </option>
                                        <?php
                                        foreach ($this->elements as $sortable) {
                                            ?>
                                            <option value="<?php echo $sortable->reference_id; ?>" <?php echo $this->form->initial_sort_order2 == $sortable->reference_id ? ' selected="selected"' : ''; ?>>
                                                <?php echo htmlentities($sortable->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </value>
                                                <?php
                                        }
                                        ?>
                                    </select>
                                    <select class="form-select-sm" name="initial_sort_order3" id="initial_sort_order3"
                                        style="max-width: 200px;">
                                        <option value="-1">
                                            <?php echo Text::_('COM_CONTENTBUILDER_NONE'); ?>
                                        </option>
                                        <?php
                                        foreach ($this->elements as $sortable) {
                                            ?>
                                            <option value="<?php echo $sortable->reference_id; ?>" <?php echo $this->form->initial_sort_order3 == $sortable->reference_id ? ' selected="selected"' : ''; ?>>
                                                <?php echo htmlentities($sortable->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </value>
                                                <?php
                                        }
                                        ?>
                                    </select>
                                    <div></div>
                                    <input class="form-check-input" type="radio" name="initial_order_dir"
                                        id="initial_order_dir" value="asc" <?php echo $this->form->initial_order_dir == 'asc' ? ' checked="checked"' : ''; ?> /> <label
                                        for="initial_order_dir">
                                        <?php echo Text::_('COM_CONTENTBUILDER_INITIAL_SORT_ORDER_ASC'); ?>
                                    </label>
                                    <input class="form-check-input" type="radio" name="initial_order_dir"
                                        id="initial_order_dir_desc" value="desc" <?php echo $this->form->initial_order_dir == 'desc' ? ' checked="checked"' : ''; ?> /> <label
                                        for="initial_order_dir_desc">
                                        <?php echo Text::_('COM_CONTENTBUILDER_INITIAL_SORT_ORDER_DESC'); ?>
                                    </label>
                                </div>
                            </fieldset>

                            <hr />

                            <fieldset>
                                <legend>
                                    <h3>
                                        <?php echo Text::_('COM_CONTENTBUILDER_BUTTONS'); ?>
                                    </h3>
                                </legend>
                                <div class="alert">
                                    <label for="save_button_title">
                                        <span class="editlinktip hasTip"
                                            title="<?php echo Text::_('COM_CONTENTBUILDER_SAVE_BUTTON_TITLE_TIP'); ?>"><b>
                                                <?php echo Text::_('COM_CONTENTBUILDER_SAVE_BUTTON_TITLE'); ?>:
                                            </b></span>
                                    </label>
                                    <input class="form-control form-control-sm" type="text" id="save_button_title"
                                        name="save_button_title"
                                        value="<?php echo htmlentities($this->form->save_button_title ?? '', ENT_QUOTES, 'UTF-8'); ?>" />

                                    <label for="apply_button_title">
                                        <span class="editlinktip hasTip"
                                            title="<?php echo Text::_('COM_CONTENTBUILDER_APPLY_BUTTON_TITLE_TIP'); ?>"><b>
                                                <?php echo Text::_('COM_CONTENTBUILDER_APPLY_BUTTON_TITLE'); ?>:
                                            </b></span>
                                    </label>
                                    <input class="form-control form-control-sm" type="text" id="apply_button_title"
                                        name="apply_button_title"
                                        value="<?php echo htmlentities($this->form->apply_button_title ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </fieldset>

                            <hr />

                            <fieldset>
                                <legend>
                                    <h3>
                                        <?php echo Text::_('COM_CONTENTBUILDER_MISC'); ?>
                                    </h3>
                                </legend>
                                <div class="alert">
                                    <input class="form-check-input" id="filter_exact_match" type="checkbox"
                                        name="filter_exact_match" value="1" <?php echo $this->form->filter_exact_match ? ' checked="checked"' : '' ?> />
                                    <label for="filter_exact_match">
                                        <span class="editlinktip hasTip"
                                            title="<?php echo Text::_('COM_CONTENTBUILDER_FILTER_EXACT_MATCH_TIP'); ?>">
                                            <?php echo Text::_('COM_CONTENTBUILDER_FILTER_EXACT_MATCH'); ?>
                                        </span>
                                    </label>

                                    <input class="form-check-input" id="use_view_name_as_title" type="checkbox"
                                        name="use_view_name_as_title" value="1" <?php echo $this->form->use_view_name_as_title ? ' checked="checked"' : '' ?> />
                                    <label for="use_view_name_as_title">
                                        <span class="editlinktip hasTip"
                                            title="<?php echo Text::_('COM_CONTENTBUILDER_USE_VIEW_NAME_AS_TITLE_TIP'); ?>">
                                            <?php echo Text::_('COM_CONTENTBUILDER_USE_VIEW_NAME_AS_TITLE'); ?>
                                        </span>
                                    </label>

                                    <input class="form-check-input" id="published_only" type="checkbox"
                                        name="published_only" value="1" <?php echo $this->form->published_only ? ' checked="checked"' : '' ?> />
                                    <label for="published_only">
                                        <span class="editlinktip hasTip"
                                            title="<?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED_ONLY_TIP'); ?>">
                                            <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED_ONLY'); ?>
                                        </span>
                                    </label>

                                    <input class="form-check-input" type="checkbox" id="allow_external_filter"
                                        name="allow_external_filter" value="1" <?php echo $this->form->allow_external_filter ? ' checked="checked"' : '' ?> />
                                    <label for="allow_external_filter">
                                        <span class="editlinktip hasTip"
                                            title="<?php echo Text::_('COM_CONTENTBUILDER_ALLOW_EXTERNAL_FILTER_TIP'); ?>">
                                            <?php echo Text::_('COM_CONTENTBUILDER_ALLOW_EXTERNAL_FILTER'); ?>
                                        </span>
                                    </label>
                                </div>
                            </fieldset>

                        </div>

                    </fieldset>

                </td>
            </tr>
        </table>
        </fieldset>
        </td>
        </tr>
        <tr>
            <td valign="top">
                <table class="adminlist table table-striped">
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
                                <span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_LABEL_TIP'); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_LABEL'); ?>
                                </span>
                            </th>
                            <th>
                                <span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_LIST_INCLUDE_TIP'); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_LIST_INCLUDE'); ?>
                                </span>
                            </th>
                            <th>
                                <span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_SEARCH_INCLUDE_TIP'); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_SEARCH_INCLUDE'); ?>
                                </span>
                            </th>
                            <th>
                                <span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_LINKABLE_TIP'); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_LINKABLE'); ?>
                                </span>
                            </th>
                            <th>
                                <span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_EDITABLE_TIP'); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_EDITABLE'); ?>
                                </span>
                            </th>
                            <th>
                                <span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_LIST_WORDWRAP_TIP'); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_LIST_WORDWRAP'); ?>
                                </span>
                            </th>
                            <th width="150">
                                <span class="editlinktip hasTip"
                                    title="<?php echo contentbuilder::allhtmlentities(Text::_('COM_CONTENTBUILDER_LIST_ITEM_WRAPPER_TIP')); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_LIST_ITEM_WRAPPER'); ?>
                                </span>
                            </th>
                            <th>
                                <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED'); ?>
                            </th>
                            <th width="120">
                                <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_ORDERBY'), 'ordering', 'desc', @$this->lists['order'], 'edit'); ?>
                                <?php //TODO: dragndrop if ($this->ordering) echo HTMLHelper::_('grid.order',  $this->elements );   ?>
                                <?php if ($this->ordering) echo HTMLHelper::_('grid.order',  $this->elements ); ?>
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
                        $list_include = contentbuilder_helpers::listIncludeInList($row, $i);
                        $search_include = contentbuilder_helpers::listIncludeInSearch($row, $i);
                        $linkable = contentbuilder_helpers::listLinkable($row, $i);
                        $editable = contentbuilder_helpers::listEditable($row, $i);
                        ?>
                        <tr class="<?php echo "row$k"; ?>">
                            <td valign="top">
                                <?php echo $row->id; ?>
                            </td>
                            <td valign="top">
                                <?php echo $checked; ?>
                            </td>
                            <td width="150" valign="top">
                                <div style="cursor:pointer;width: 100%;display:block;"
                                    id="itemLabels_<?php echo $row->id ?>"
                                    onclick="document.getElementById('itemLabels<?php echo $row->id ?>').style.display='block';this.style.display='none';document.getElementById('itemLabels<?php echo $row->id ?>').focus();">
                                    <b>
                                        <?php echo htmlentities($row->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </b>
                                </div>
                                <input class="form-control form-control-sm"
                                    onblur="if(this.value=='') {this.value = 'Unnamed';} this.style.display='none';document.getElementById('itemLabels_<?php echo $row->id ?>').innerHTML='<b>'+this.value+'<b/>';document.getElementById('itemLabels_<?php echo $row->id ?>').style.display='block';"
                                    id="itemLabels<?php echo $row->id ?>" type="text" style="display:none; width: 100%;"
                                    name="itemLabels[<?php echo $row->id ?>]"
                                    value="<?php echo htmlentities($row->label ?? '', ENT_QUOTES, 'UTF-8') ?>" />

                                <br />

                                <select class="form-select-sm" style="max-width: 125px;"
                                    id="itemOrderTypes<?php echo $row->id ?>" name="itemOrderTypes[<?php echo $row->id ?>]">
                                    <option value=""> -
                                        <?php echo Text::_('COM_CONTENTBUILDER_ORDER_TYPES'); ?> -
                                    </option>
                                    <option value="CHAR" <?php echo $row->order_type == 'CHAR' ? ' selected="selected"' : '' ?>>
                                        <?php echo Text::_('COM_CONTENTBUILDER_ORDER_TYPES_TEXT'); ?>
                                    </option>
                                    <option value="DATETIME" <?php echo $row->order_type == 'DATETIME' ? ' selected="selected"' : '' ?>>
                                        <?php echo Text::_('COM_CONTENTBUILDER_ORDER_TYPES_DATETIME'); ?>
                                    </option>
                                    <option value="DATE" <?php echo $row->order_type == 'DATE' ? ' selected="selected"' : '' ?>>
                                        <?php echo Text::_('COM_CONTENTBUILDER_ORDER_TYPES_DATE'); ?>
                                    </option>
                                    <option value="TIME" <?php echo $row->order_type == 'TIME' ? ' selected="selected"' : '' ?>>
                                        <?php echo Text::_('COM_CONTENTBUILDER_ORDER_TYPES_TIME'); ?>
                                    </option>
                                    <option value="UNSIGNED" <?php echo $row->order_type == 'UNSIGNED' ? ' selected="selected"' : '' ?>>
                                        <?php echo Text::_('COM_CONTENTBUILDER_ORDER_TYPES_INTEGER'); ?>
                                    </option>
                                    <option value="DECIMAL" <?php echo $row->order_type == 'DECIMAL' ? ' selected="selected"' : '' ?>>
                                        <?php echo Text::_('COM_CONTENTBUILDER_ORDER_TYPES_DECIMAL'); ?>
                                    </option>
                                </select>

                            </td>
                            <td valign="top">
                                <?php echo $list_include; ?>
                            </td>
                            <td valign="top">
                                <?php echo $search_include; ?>
                            </td>
                            <td valign="top">
                                <?php echo $linkable; ?>
                            </td>
                            <td valign="top">
                                <?php echo $editable; ?>
                                <?php
                                if ($row->editable && !$this->form->edit_by_type) {
                                    echo '<br/><br/>[<a href="index.php?option=com_contentbuilder&amp;controller=elementoptions&amp;tmpl=component&amp;element_id=' . $row->id . '&amp;id=' . $this->form->id . '" title="" data-bs-toggle="modal" data-bs-target="#text-type-modal">' . $row->type . '</a>]';
                                }
                                ?>
                            </td>
                            <td valign="top">
                                <input class="form-control form-control-sm w-100" type="text" style="width: 20px;"
                                    name="itemWordwrap[<?php echo $row->id ?>]"
                                    value="<?php echo htmlentities($row->wordwrap ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                            </td>
                            <td valign="top">
                                <input class="form-control form-control-sm w-100" style="width: 150px;" type="text"
                                    name="itemWrapper[<?php echo $row->id ?>]"
                                    value="<?php echo htmlentities($row->item_wrapper ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                            </td>
                            <td valign="top">
                                <?php echo $published; ?>
                            </td>
                            <td class="order" width="150" valign="top">
                                <span>
                                    <?php echo $this->pagination->orderUpIcon($i, true, 'orderup', 'Move Up', $this->ordering); ?>
                                </span>
                                <span>
                                    <?php echo $this->pagination->orderDownIcon($i, $n, true, 'orderdown', 'Move Down', $this->ordering); ?>
                                </span>
                                <?php $disabled = $this->ordering ? '' : 'disabled="disabled"'; ?>
                                <input type="text" name="order[]" size="5" style="width: 20px;"
                                    value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area"
                                    style="text-align: center" />
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
        $title = Text::_('COM_CONTENTBUILDER_LIST_INTRO_TEXT');
        echo HTMLHelper::_('uitab.endTab');
        echo $cbcompat->startPanel($title, "tab2");
        $editor = Editor::getInstance(Factory::getApplication()->get('editor'));
        echo $editor->display("intro_text", $this->form->intro_text, '100%', '550', '75', '20');
        ?>

        <?php
        $title = Text::_('COM_CONTENTBUILDER_LIST_STATES');
        echo HTMLHelper::_('uitab.endTab');
        echo $cbcompat->startPanel($title, "tab1");
        ?>
        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_LIST_STATES_PUBLISHED') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_LIST_STATES_TITLE') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_LIST_STATES_COLOR') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_LIST_STATES_ACTION') ?>
                    </th>
                </tr>
            </thead>
            <?php
            foreach ($this->form->list_states as $state) {
                $k = 0;
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td>
                        <input class="form-check-input" type="checkbox"
                            name="list_states[<?php echo $state['id']; ?>][published]" value="1" <?php echo $state['published'] ? ' checked="checked"' : '' ?> />
                    </td>
                    <td>
                        <input class="form-control form-control-sm w-100" type="text"
                            name="list_states[<?php echo $state['id']; ?>][title]"
                            value="<?php echo htmlentities($state['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                    </td>
                    <td>
                        <input class="form-control form-control-sm w-100 color" type="text"
                            value="<?php echo $state['color']; ?>"
                            name="list_states[<?php echo $state['id']; ?>][color]" /><br />
                    </td>
                    <td>
                        <select class="form-select-sm" name="list_states[<?php echo $state['id']; ?>][action]">
                            <option value=""> -
                                <?php echo Text::_('COM_CONTENTBUILDER_NONE'); ?> -
                            </option>
                            <?php
                            foreach ($this->list_states_action_plugins as $list_state_action_plugin) {
                                ?>
                                <option value="<?php echo $list_state_action_plugin; ?>" <?php echo $list_state_action_plugin == $state['action'] ? ' selected="selected"' : ''; ?>>
                                    <?php echo $list_state_action_plugin; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
        </table>
        <?php
        $title = Text::_('COM_CONTENTBUILDER_DETAILS_TEMPLATE');
        echo HTMLHelper::_('uitab.endTab');
        echo $cbcompat->startPanel($title, "tab3");
        ?>
        <table width="100%" class="adminform table table-striped">
            <tr>
                <td width="20%">
                    <label for="create_sample"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_CREATE_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_CREATE'); ?><span></label>
                </td>
                <td>
                    <input class="form-check-input" id="create_sample" type="checkbox" name="create_sample" value="1" />
                    <label for="create_sample">
                        <?php echo Text::_('COM_CONTENTBUILDER_CREATE_SAMPLE'); ?>
                    </label>
                    <input class="form-check-input" <?php echo $this->form->create_articles == 1 ? ' checked="checked"' : '' ?>type="checkbox" name="create_articles" id="create_articles" value="1" /><label
                        for="create_articles">
                        <?php echo Text::_('COM_CONTENTBUILDER_CREATE_ARTICLES'); ?>
                    </label>
                </td>
                <td width="20%">
                    <label for="delete_articles"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_DELETE_ARTICLES_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_DELETE_ARTICLES'); ?>
                        </span></label>
                </td>
                <td>
                    <input class="form-check-input" type="radio" value="1" name="delete_articles" id="delete_articles"
                        <?php echo $this->form->delete_articles ? ' checked="checked"' : '' ?> /> <label
                        for="delete_articles">
                        <?php echo Text::_('COM_CONTENTBUILDER_YES'); ?>
                    </label>
                    <input class="form-check-input" type="radio" value="0" name="delete_articles"
                        id="delete_articles_no" <?php echo !$this->form->delete_articles ? ' checked="checked"' : '' ?> /> <label for="delete_articles_no">
                        <?php echo Text::_('COM_CONTENTBUILDER_NO'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td width="20%">
                    <label for="title_field"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_TITLE_FIELD_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_TITLE_FIELD'); ?>
                        </span></label>
                </td>
                <td>
                    <select class="form-select-sm" name="title_field" id="title_field">
                        <?php
                        foreach ($this->all_elements as $sortable) {
                            ?>
                            <option value="<?php echo $sortable->reference_id; ?>" <?php echo $this->form->title_field == $sortable->reference_id ? ' selected="selected"' : ''; ?>>
                                <?php echo htmlentities($sortable->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </value>
                                <?php
                        }
                        ?>
                    </select>
                </td>
                <td width="20%">
                    <label for="default_category"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_CATEGORY_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_CATEGORY'); ?>
                        </span></label>
                </td>
                <td>
                    <?php
                    ?>
                    <select class="form-select-sm" id="default_category" name="sectioncategories">
                        <?php
                        foreach ($this->form->sectioncategories as $category) {
                            ?>
                            <option <?php echo $this->form->default_category == $category->value ? ' selected="selected"' : '' ?>value="<?php echo $category->value; ?>">
                                <?php echo htmlentities($category->text ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                    <?php
                    ?>
                </td>
            </tr>
            <tr>
                <td width="20%" valign="top">
                    <label for="default_lang_code"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_LANG_CODE_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_LANG_CODE'); ?>
                        </span></label>
                </td>
                <td valign="top">
                    <select class="form-select-sm" name="default_lang_code" id="default_lang_code">
                        <option value="*">
                            <?php echo Text::_('COM_CONTENTBUILDER_ANY'); ?>
                        </option>
                        <?php
                        foreach ($this->form->language_codes as $lang_code) {
                            ?>
                            <option value="<?php echo $lang_code ?>" <?php echo $lang_code == $this->form->default_lang_code ? ' selected="selected"' : ''; ?>>
                                <?php echo $lang_code; ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                    <br /><br />
                    <label for="article_record_impact_language"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_ARTICLE_RECORD_IMPACT_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_ARTICLE_RECORD_IMPACT'); ?>
                        </span></label>
                    <input class="form-check-input" <?php echo $this->form->article_record_impact_language ? 'checked="checked" ' : '' ?>type="radio" name="article_record_impact_language"
                        id="article_record_impact_language" value="1" />
                    <label for="article_record_impact_language_yes">
                        <?php echo Text::_('COM_CONTENTBUILDER_YES'); ?>
                    </label>
                    <input class="form-check-input" <?php echo !$this->form->article_record_impact_language ? 'checked="checked" ' : '' ?>type="radio" name="article_record_impact_language"
                        id="article_record_impact_language_no" value="0" />
                    <label for="article_record_impact_language_no">
                        <?php echo Text::_('COM_CONTENTBUILDER_NO'); ?>
                    </label>
                </td>
                <td width="20%" valign="top">
                    <label for="default_lang_code_ignore_yes"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_LANG_CODE_IGNORE_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_LANG_CODE_IGNORE'); ?>
                        </span></label>
                </td>
                <td valign="top">
                    <input class="form-check-input" <?php echo $this->form->default_lang_code_ignore ? 'checked="checked" ' : '' ?>type="radio" name="default_lang_code_ignore"
                        id="default_lang_code_ignore_yes" value="1" />
                    <label for="default_lang_code_ignore_yes">
                        <?php echo Text::_('COM_CONTENTBUILDER_YES'); ?>
                    </label>

                    <input class="form-check-input" <?php echo !$this->form->default_lang_code_ignore ? 'checked="checked" ' : '' ?>type="radio" name="default_lang_code_ignore"
                        id="default_lang_code_ignore_no" value="0" />
                    <label for="default_lang_code_ignore_no">
                        <?php echo Text::_('COM_CONTENTBUILDER_NO'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td width="20%" valign="top">
                    <label for="default_publish_up_days"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_PUBLISH_UP_DAYS_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_PUBLISH_UP_DAYS'); ?>
                        </span></label>
                </td>
                <td valign="top">
                    <input class="form-control form-control-sm w-100" type="text" name="default_publish_up_days"
                        id="default_publish_up_days" value="<?php echo $this->form->default_publish_up_days; ?>" />
                    <br /><br />
                    <label for="article_record_impact_publish"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_ARTICLE_RECORD_PUBLISH_IMPACT_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_ARTICLE_RECORD_PUBLISH_IMPACT'); ?>
                        </span></label>
                    <input class="form-check-input" <?php echo $this->form->article_record_impact_publish ? 'checked="checked" ' : '' ?>type="radio" name="article_record_impact_publish"
                        id="article_record_impact_publish" value="1" />
                    <label for="article_record_impact_publish_yes">
                        <?php echo Text::_('COM_CONTENTBUILDER_YES'); ?>
                    </label>
                    <input class="form-check-input" <?php echo !$this->form->article_record_impact_publish ? 'checked="checked" ' : '' ?>type="radio" name="article_record_impact_publish"
                        id="article_record_impact_publish_no" value="0" />
                    <label for="article_record_impact_publish_no">
                        <?php echo Text::_('COM_CONTENTBUILDER_NO'); ?>
                    </label>

                </td>
                <td width="20%" valign="top">
                    <label for="default_publish_down_days"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_PUBLISH_DOWN_DAYS_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_PUBLISH_DOWN_DAYS'); ?>
                        </span></label>
                </td>
                <td valign="top">
                    <input class="form-control form-control-sm w-100" type="text" name="default_publish_down_days"
                        id="default_publish_down_days" value="<?php echo $this->form->default_publish_down_days; ?>" />
                </td>

            </tr>
            <tr>
                <td width="20%">
                    <label for="default_access"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_ACCESS_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_ACCESS'); ?>
                        </span></label>
                </td>
                <td>
                    <?php
                    ?>
                    <?php echo HTMLHelper::_('access.level', 'default_access', $this->form->default_access, '', array(), 'default_access'); ?>
                    <?php
                    ?>
                </td>
                <td width="20%">
                    <label for="default_featured"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_FEATURED_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_DEFAULT_FEATURED'); ?>
                        </span></label>
                </td>
                <td>
                    <input class="form-check-input" class="form-check-input" <?php echo $this->form->default_featured ? 'checked="checked" ' : '' ?>type="radio" name="default_featured" id="default_featured"
                        value="1" />
                    <label for="default_featured">
                        <?php echo Text::_('COM_CONTENTBUILDER_YES'); ?>
                    </label>

                    <input class="form-check-input" class="form-check-input" <?php echo !$this->form->default_featured ? 'checked="checked" ' : '' ?>type="radio" name="default_featured" id="default_featured_no"
                        value="0" />
                    <label for="default_featured_no">
                        <?php echo Text::_('COM_CONTENTBUILDER_NO'); ?>
                    </label>

                </td>
            </tr>
            <tr>
                <td width="20%">
                    <label for="auto_publish"><span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_AUTO_PUBLISH_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_AUTO_PUBLISH'); ?>
                        </span></label>
                </td>
                <td>
                    <input class="form-check-input" <?php echo $this->form->auto_publish == 1 ? ' checked="checked"' : '' ?>type="checkbox" name="auto_publish" id="auto_publish" value="1" />
                </td>
                <td width="20%">
                    <?php
                    if ($this->form->edit_by_type && $this->form->type == 'com_breezingforms') {
                        ?>
                        <label for="protect_upload_directory"><span class="editlinktip hasTip"
                                title="<?php echo Text::_('COM_CONTENTBUILDER_UPLOAD_DIRECTORY_TYPE_TIP'); ?>">
                                <?php echo Text::_('COM_CONTENTBUILDER_PROTECT_UPLOAD_DIRECTORY'); ?>
                            </span></label>
                        <?php
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ($this->form->edit_by_type && $this->form->type == 'com_breezingforms') {
                        ?>
                        <input class="form-check-input" type="checkbox" value="1" name="protect_upload_directory"
                            id="protect_upload_directory" <?php echo trim($this->form->protect_upload_directory) ? ' checked="checked"' : ''; ?> />
                        <?php
                    }
                    ?>
                </td>
            </tr>
        </table>

        <?php
        $editor = Editor::getInstance(Factory::getApplication()->get('editor'));
        echo $editor->display("details_template", $this->form->details_template, '100%', '550', '75', '20');

        $title = Text::_('COM_CONTENTBUILDER_DETAILS_PREPARE');
        echo HTMLHelper::_('uitab.endTab');
        echo $cbcompat->startPanel($title, "tab4");
        if (trim($this->form->details_prepare ?? '') == '') {
            $this->form->details_prepare = '// Here you may alter labels and values for each item before it gets rendered through your details template.' . "\n";
            $this->form->details_prepare .= '// For example:' . "\n";
            $this->form->details_prepare .= '// $items["ITEMNAME"]["value"] = "<b>".$items["ITEMNAME"]["value"]."</b>";' . "\n";
            $this->form->details_prepare .= '// $items["ITEMNAME"]["label"] = "<i>".$items["ITEMNAME"]["label"]."</i>";' . "\n";
        }

        $params = array('syntax' => 'php');
        $editor = Editor::getInstance('codemirror');
        echo $editor->display("details_prepare", $this->form->details_prepare, '100%', '550', '75', '20', false, null, null, null, $params);

        //echo '<textarea name="details_prepare" style="width:100%;height: 500px;">'.htmlentities($this->form->details_prepare, ENT_QUOTES, 'UTF-8').'</textarea>';
        ?>
        <?php
        echo HTMLHelper::_('uitab.endTab');
        $title = Text::_('COM_CONTENTBUILDER_EDITABLE_TEMPLATE');
        echo $cbcompat->startPanel($title, "tab5");
        if ($this->form->edit_by_type && $this->form->type == 'com_breezingforms') {
            echo Text::_('COM_CONTENTBUILDER_EDITABLE_TEMPLATE_PROVIDED_BY_BREEZINGFORMS');
            echo '<input type="hidden" name="editable_template" value="{BreezingForms: ' . (isset($this->form->type_name) ? $this->form->type_name : '') . '}"/>';
            //echo '<input type="hidden" name="protect_upload_directory" value="'.(trim($this->form->protect_upload_directory) ? 1 : 0).'"/>'; 
            echo '<input type="hidden" name="upload_directory" value="' . (trim($this->form->upload_directory) ? trim($this->form->upload_directory) : JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'upload') . '"/>';
        } else {
            ?>

            <label for="upload_directory"><span class="editlinktip hasTip"
                    title="<?php echo Text::_('COM_CONTENTBUILDER_UPLOAD_DIRECTORY_TIP'); ?>">
                    <?php echo Text::_('COM_CONTENTBUILDER_UPLOAD_DIRECTORY'); ?>
                </span></label>
            <br />
            <input class="form-control form-control-sm" style="width: 50%;" type="text"
                value="<?php echo trim($this->form->upload_directory) ? trim($this->form->upload_directory) : JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'upload'; ?>"
                name="upload_directory" id="upload_directory" />
            <br />
            <br />
            <input class="form-check-input" type="checkbox" value="1" name="protect_upload_directory"
                id="protect_upload_directory" <?php echo trim($this->form->protect_upload_directory) ? ' checked="checked"' : ''; ?> /> <label for="protect_upload_directory">
                <?php echo Text::_('COM_CONTENTBUILDER_PROTECT_UPLOAD_DIRECTORY'); ?>
            </label>
            <br />
            <br />
            <input class="form-check-input" type="checkbox" name="create_editable_sample" id="editable_sample" value="1" />
            <label for="editable_sample">
                <?php echo Text::_('COM_CONTENTBUILDER_CREATE_EDITABLE_SAMPLE'); ?>
            </label>
            <br />
            <br />
            <?php
        }

        $editor = Editor::getInstance(Factory::getApplication()->get('editor'));
        echo $editor->display("editable_template", $this->form->editable_template, '100%', '550', '75', '20');
        ?>
        <?php
        $title = Text::_('COM_CONTENTBUILDER_EDITABLE_PREPARE');
        echo HTMLHelper::_('uitab.endTab');
        echo $cbcompat->startPanel($title, "tab6");
        if ($this->form->edit_by_type) {
            echo Text::_('COM_CONTENTBUILDER_EDITABLE_TEMPLATE_PROVIDED_BY_BREEZINGFORMS');
            echo '<input type="hidden" name="editable_prepare" value="' . htmlentities($this->form->editable_prepare ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
        } else {
            if (trim($this->form->editable_prepare ?? '') == '') {
                $this->form->editable_prepare = '// Here you may alter labels and values for each item before it gets rendered through your editable template.' . "\n";
                $this->form->editable_prepare .= '// For example:' . "\n";
                $this->form->editable_prepare .= '// $items["ITEMNAME"]["value"] = $items["ITEMNAME"]["value"];' . "\n";
                $this->form->editable_prepare .= '// $items["ITEMNAME"]["label"] = "<i>".$items["ITEMNAME"]["label"]."</i>";' . "\n";
            }

            $params = array('syntax' => 'php');
            $editor = Editor::getInstance('codemirror');
            echo $editor->display("editable_prepare", $this->form->editable_prepare, '100%', '550', '75', '20', false, null, null, null, $params);
        }

        echo HTMLHelper::_('uitab.endTab');
        $title = Text::_('COM_CONTENTBUILDER_EMAIL_TEMPLATES');
        echo $cbcompat->startPanel($title, "tab7");

        if ($this->form->edit_by_type) {
            echo Text::_('COM_CONTENTBUILDER_EDITABLE_TEMPLATE_PROVIDED_BY_BREEZINGFORMS');
            echo '<input type="hidden" name="email_admin_template" value="' . htmlentities($this->form->email_admin_template ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_template" value="' . htmlentities($this->form->email_template ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_admin_subject" value="' . htmlentities($this->form->email_admin_subject ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_admin_alternative_from" value="' . htmlentities($this->form->email_admin_alternative_from ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_admin_alternative_fromname" value="' . htmlentities($this->form->email_admin_alternative_fromname ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_admin_recipients" value="' . htmlentities($this->form->email_admin_recipients ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_admin_recipients_attach_uploads" value="' . htmlentities($this->form->email_admin_recipients_attach_uploads ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_admin_html" value="' . htmlentities($this->form->email_admin_html ?? '', ENT_QUOTES, 'UTF-8') . '"/>';

            echo '<input type="hidden" name="email_subject" value="' . htmlentities($this->form->email_subject ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_alternative_from" value="' . htmlentities($this->form->email_alternative_from ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_alternative_fromname" value="' . htmlentities($this->form->email_alternative_fromname ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_recipients" value="' . htmlentities($this->form->email_recipients ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_recipients_attach_uploads" value="' . htmlentities($this->form->email_recipients_attach_uploads ?? '', ENT_QUOTES, 'UTF-8') . '"/>';
            echo '<input type="hidden" name="email_html" value="' . htmlentities($this->form->email_html ?? '', ENT_QUOTES, 'UTF-8') . '"/>';


        } else {

            $title = Text::_('COM_CONTENTBUILDER_EMAIL_ADMINS');

            ?>
            <div id="email_admins" style="cursor:pointer; width: 100%; background-color: #ffffff;"
                onclick="if(document.adminForm.email_admins.value=='none'){document.adminForm.email_admins.value='';document.getElementById('email_admins_div').style.display='';}else{document.adminForm.email_admins.value='none';document.getElementById('email_admins_div').style.display='none';}">
                <h3>
                    <?php echo $title; ?>
                </h3>
            </div>
            <div id="email_admins_div"
                style="display:<?php echo Factory::getApplication()->getSession()->get('email_admins', '', 'com_contentbuilder'); ?>">
                <table width="100%" class="adminform table table-striped">
                    <tr>
                        <td width="20%">
                            <label for="email_admin_subject"><span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_EMAIL_SUBJECT_TIP'); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_SUBJECT'); ?>
                                </span></label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_admin_subject" type="text"
                                name="email_admin_subject"
                                value="<?php echo htmlentities($this->form->email_admin_subject ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td width="20%">
                            <label for="email_admin_alternative_from">
                                <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_ALTERNATIVE_FROM'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_admin_alternative_from" type="text"
                                name="email_admin_alternative_from"
                                value="<?php echo htmlentities($this->form->email_admin_alternative_from ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <label for="email_admin_alternative_fromname">
                                <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_ALTERNATIVE_FROMNAME'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_admin_alternative_fromname"
                                type="text" name="email_admin_alternative_fromname"
                                value="<?php echo htmlentities($this->form->email_admin_alternative_fromname ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td width="20%">
                            <label for="email_admin_recipients"><span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_EMAIL_RECIPIENTS_TIP'); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_RECIPIENTS'); ?>
                                </span></label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_admin_recipients" type="text"
                                name="email_admin_recipients"
                                value="<?php echo htmlentities($this->form->email_admin_recipients ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <label for="email_admin_recipients_attach_uploads"><span class="editlinktip hasTip"
                                    title="<?php echo Text::_('COM_CONTENTBUILDER_EMAIL_ATTACH_UPLOADS_TIP'); ?>">
                                    <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_ATTACH_UPLOADS'); ?>
                                </span></label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_admin_recipients_attach_uploads"
                                type="text" name="email_admin_recipients_attach_uploads"
                                value="<?php echo htmlentities($this->form->email_admin_recipients_attach_uploads ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td width="20%">
                            <label for="email_admin_html">
                                <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_HTML'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-check-input" id="email_admin_html" type="checkbox" name="email_admin_html"
                                value="1" <?php echo $this->form->email_admin_html ? ' checked="checked"' : ''; ?> />
                        </td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <label for="email_admin_create_sample">
                                <?php echo Text::_('COM_CONTENTBUILDER_CREATE_EDITABLE_SAMPLE'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-check-input" id="email_admin_create_sample" type="checkbox"
                                name="email_admin_create_sample" value="1" />
                        </td>
                        <td width="20%">
                        </td>
                        <td>
                        </td>
                    </tr>
                </table>

                <?php
                $params = array('syntax' => 'html');
                $editor = Editor::getInstance('codemirror');
                echo $editor->display("email_admin_template", $this->form->email_admin_template, '100%', '550', '75', '20', false, null, null, null, $params);
                ?>
            </div>
            <?php

            $title = Text::_('COM_CONTENTBUILDER_EMAIL_USERS');

            ?>
            <div id="email_users" style="cursor:pointer; width: 100%; background-color: #ffffff;">
                <h3>
                    <?php echo $title; ?>
                </h3>
            </div>
            <div id="email_users_div">
                <table width="100%" class="adminform table table-striped">
                    <tr>
                        <td width="20%">
                            <label for="email_subject">
                                <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_SUBJECT'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_subject" type="text"
                                name="email_subject"
                                value="<?php echo htmlentities($this->form->email_subject ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td width="20%">
                            <label for="email_alternative_from">
                                <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_ALTERNATIVE_FROM'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_alternative_from" type="text"
                                name="email_alternative_from"
                                value="<?php echo htmlentities($this->form->email_alternative_from ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <label for="email_alternative_fromname">
                                <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_ALTERNATIVE_FROMNAME'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_alternative_fromname" type="text"
                                name="email_alternative_fromname"
                                value="<?php echo htmlentities($this->form->email_alternative_fromname ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td width="20%">
                            <label for="email_recipients">
                                <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_RECIPIENTS'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_recipients" type="text"
                                name="email_recipients"
                                value="<?php echo htmlentities($this->form->email_recipients ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <label for="email_recipients_attach_uploads">
                                <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_ATTACH_UPLOADS'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-control form-control-sm w-100" id="email_recipients_attach_uploads"
                                type="text" name="email_recipients_attach_uploads"
                                value="<?php echo htmlentities($this->form->email_recipients_attach_uploads ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </td>
                        <td width="20%">
                            <label for="email_html">
                                <?php echo Text::_('COM_CONTENTBUILDER_EMAIL_HTML'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-check-input" id="email_html" type="checkbox" name="email_html" value="1"
                                <?php echo $this->form->email_html ? ' checked="checked"' : ''; ?> />
                        </td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <label for="email_create_sample">
                                <?php echo Text::_('COM_CONTENTBUILDER_CREATE_EDITABLE_SAMPLE'); ?>
                            </label>
                        </td>
                        <td>
                            <input class="form-check-input" id="email_create_sample" type="checkbox"
                                name="email_create_sample" value="1" />
                        </td>
                        <td width="20%">
                        </td>
                        <td>
                        </td>
                    </tr>
                </table>

                <?php
                $params = array('syntax' => 'html');
                $editor = Editor::getInstance('codemirror');
                echo $editor->display("email_template", $this->form->email_template, '100%', '550', '75', '20', false, null, null, null, $params);
                ?>
            </div>
            <?php
        }

        echo HTMLHelper::_('uitab.endTab');
        $title = Text::_('COM_CONTENTBUILDER_PERMISSIONS');
        echo $cbcompat->startPanel($title, "tab8");

        $sliders = CBTabs::getInstance('perm-pane', array('startOffset' => Factory::getApplication()->getSession()->get('slideStartOffset', 1), 'startTransition' => 0));

        echo $sliders->startPane("perm-pane");

        $title = Text::_('COM_CONTENTBUILDER_PERMISSIONS_FRONTEND');
        echo $sliders->startPanel($title, "permtab1");
        ?>
        <table class="adminlist table table-striped">
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="own_only_fe">
                        <span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_PERM_OWN_OWNLY_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_OWN_OWNLY'); ?>
                        </span>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="own_only_fe" id="own_only_fe" value="1" <?php echo $this->form->own_only_fe ? ' checked="checked"' : ''; ?> />
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="limited_article_options_fe">
                        <span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_PERM_LIMITED_ARTICLE_OPTIONS_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIMITED_ARTICLE_OPTIONS'); ?>
                        </span>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="limited_article_options_fe"
                        id="limited_article_options_fe" value="1" <?php echo $this->form->limited_article_options_fe ? ' checked="checked"' : ''; ?> />
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="own_fe_view">
                        <span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_PERM_OWN_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_OWN'); ?>
                        </span>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="own_fe[listaccess]" id="own_fe_listaccess"
                        value="1" <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['listaccess']) && $this->form->config['own_fe']['listaccess'] ? ' checked="checked"' : ''; ?> /> <label
                        for="own_fe_listaccess">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIST_ACCESS'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own_fe[view]" id="own_fe_view" value="1" <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['view']) && $this->form->config['own_fe']['view'] ? ' checked="checked"' : ''; ?> /> <label for="own_fe_view">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VIEW'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own_fe[new]" id="own_fe_new" value="1" <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['new']) && $this->form->config['own_fe']['new'] ? ' checked="checked"' : ''; ?> /> <label for="own_fe_new">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_NEW'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own_fe[edit]" id="own_fe_edit" value="1" <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['edit']) && $this->form->config['own_fe']['edit'] ? ' checked="checked"' : ''; ?> /> <label for="own_fe_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_EDIT'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own_fe[delete]" id="own_fe_delete" value="1"
                        <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['delete']) && $this->form->config['own_fe']['delete'] ? ' checked="checked"' : ''; ?> /> <label for="own_fe_delete">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_DELETE'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own_fe[state]" id="own_fe_state" value="1"
                        <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['state']) && $this->form->config['own_fe']['state'] ? ' checked="checked"' : ''; ?> /> <label for="own_fe_state">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_STATE'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own_fe[publish]" id="own_fe_publish" value="1"
                        <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['publish']) && $this->form->config['own_fe']['publish'] ? ' checked="checked"' : ''; ?> /> <label for="own_fe_publish">
                        <?php echo Text::_('PUBLISH'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own_fe[fullarticle]" id="own_fe_fullarticle"
                        value="1" <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['fullarticle']) && $this->form->config['own_fe']['fullarticle'] ? ' checked="checked"' : ''; ?> /> <label
                        for="own_fe_fullarticle">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_FULL_ARTICLE'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own_fe[language]" id="own_fe_language"
                        value="1" <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['language']) && $this->form->config['own_fe']['language'] ? ' checked="checked"' : ''; ?> /> <label
                        for="own_fe_language">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_CHANGE_LANGUAGE'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own_fe[rating]" id="own_fe_rating" value="1"
                        <?php echo isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']) && isset($this->form->config['own_fe']['rating']) && $this->form->config['own_fe']['rating'] ? ' checked="checked"' : ''; ?> /> <label for="own_fe_rating">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_RATING'); ?>
                    </label>
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="show_all_languages_fe">
                        <span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_PERM_SHOW_ALL_LANGUAGES_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_SHOW_ALL_LANGUAGES'); ?>
                        </span>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="show_all_languages_fe"
                        id="show_all_languages_fe" value="1" <?php echo $this->form->show_all_languages_fe ? ' checked="checked"' : ''; ?> />
                </td>
            </tr>
            <?php
            if ($this->form->edit_by_type) {
                ?>
                <tr class="row0">
                    <td width="20%" align="right" class="key">
                        <label for="force_login">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_FORCE_LOGIN'); ?>
                        </label>
                    </td>
                    <td>
                        <input class="form-check-input" type="checkbox" name="force_login" id="force_login" value="1" <?php echo $this->form->force_login ? ' checked="checked"' : '' ?> />
                    </td>
                </tr>
                <tr class="row0">
                    <td width="20%" align="right" class="key">
                        <label for="force_url">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_FORCE_URL'); ?>
                        </label>
                    </td>
                    <td>
                        <input style="width: 100%;" id="force_url" name="force_url" type="text"
                            value="<?php echo htmlentities($this->form->force_url ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_GROUP') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIST_ACCESS') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VIEW') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_NEW') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_EDIT') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_DELETE') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_STATE') ?>
                    </th>
                    <th>
                        <?php echo Text::_('PUBLISH') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_FULL_ARTICLE') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_CHANGE_LANGUAGE') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_RATING') ?>
                    </th>
                </tr>
            </thead>
            <tr>
                <td style="background-color: #F0F0F0"></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="listaccess" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="view" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="new" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="edit" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="delete" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="state" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="publish" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="fullarticle" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="language" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'fe')" value="rating" /></td>
            </tr>

            <?php
            foreach ($this->gmap as $entry) {
                $k = 0;
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td>
                        <?php echo $entry->text; ?>
                    </td>
                    <td><input class="form-check-input" type="checkbox"
                            name="perms_fe[<?php echo $entry->value; ?>][listaccess]" value="1" <?php echo !$this->form->id ? ' checked="checked"' : (isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['listaccess']) && $this->form->config['permissions_fe'][$entry->value]['listaccess'] ? ' checked="checked"' : ''); ?> /></td>
                    <td><input class="form-check-input" type="checkbox" name="perms_fe[<?php echo $entry->value; ?>][view]"
                            value="1" <?php echo !$this->form->id ? ' checked="checked"' : (isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['view']) && $this->form->config['permissions_fe'][$entry->value]['view'] ? ' checked="checked"' : ''); ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox" name="perms_fe[<?php echo $entry->value; ?>][new]"
                            value="1" <?php echo isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['new']) && $this->form->config['permissions_fe'][$entry->value]['new'] ? ' checked="checked"' : ''; ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox" name="perms_fe[<?php echo $entry->value; ?>][edit]"
                            value="1" <?php echo isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['edit']) && $this->form->config['permissions_fe'][$entry->value]['edit'] ? ' checked="checked"' : ''; ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox"
                            name="perms_fe[<?php echo $entry->value; ?>][delete]" value="1" <?php echo isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['delete']) && $this->form->config['permissions_fe'][$entry->value]['delete'] ? ' checked="checked"' : ''; ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox" name="perms_fe[<?php echo $entry->value; ?>][state]"
                            value="1" <?php echo isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['state']) && $this->form->config['permissions_fe'][$entry->value]['state'] ? ' checked="checked"' : ''; ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox"
                            name="perms_fe[<?php echo $entry->value; ?>][publish]" value="1" <?php echo isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['publish']) && $this->form->config['permissions_fe'][$entry->value]['publish'] ? ' checked="checked"' : ''; ?> /></td>
                    <td><input class="form-check-input" type="checkbox"
                            name="perms_fe[<?php echo $entry->value; ?>][fullarticle]" value="1" <?php echo isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['fullarticle']) && $this->form->config['permissions_fe'][$entry->value]['fullarticle'] ? ' checked="checked"' : ''; ?> /></td>
                    <td><input class="form-check-input" type="checkbox"
                            name="perms_fe[<?php echo $entry->value; ?>][language]" value="1" <?php echo isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['language']) && $this->form->config['permissions_fe'][$entry->value]['language'] ? ' checked="checked"' : ''; ?> /></td>
                    <td><input class="form-check-input" type="checkbox"
                            name="perms_fe[<?php echo $entry->value; ?>][rating]" value="1" <?php echo isset($this->form->config['permissions_fe']) && isset($this->form->config['permissions_fe'][$entry->value]) && isset($this->form->config['permissions_fe'][$entry->value]['rating']) && $this->form->config['permissions_fe'][$entry->value]['rating'] ? ' checked="checked"' : ''; ?> />
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
        </table>
        <?php
        echo $sliders->endPanel();

        /*  MODIF XDA - GILLES (REMOVE : PERMISSION - BACKEND BOTTON), */
        /* Supprime le bouton PERMISSION - BACKEND, laisse FRONTEND seulement pour les droits */

        /*
        $title = Text::_('COM_CONTENTBUILDER_PERMISSIONS_BACKEND');
        echo $sliders->startPanel($title, "permtab0");
        ?>
        <table class="adminlist table table-striped">
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="own_only">
                        <span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_PERM_OWN_OWNLY_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_OWN_OWNLY'); ?>
                        </span>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="own_only" id="own_only" value="1" <?php echo $this->form->own_only ? ' checked="checked"' : ''; ?> />
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="limited_article_options">
                        <span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_PERM_LIMITED_ARTICLE_OPTIONS_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIMITED_ARTICLE_OPTIONS'); ?>
                        </span>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="limited_article_options"
                        id="limited_article_options" value="1" <?php echo $this->form->limited_article_options ? ' checked="checked"' : ''; ?> />
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="own_view">
                        <span class="editlinktip hasTip"
                            title="<?php echo Text::_('COM_CONTENTBUILDER_PERM_OWN_TIP'); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_OWN'); ?>
                        </span>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="own[listaccess]" id="own_listaccess" value="1"
                        <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['listaccess']) && $this->form->config['own']['listaccess'] ? ' checked="checked"' : ''; ?> /><label for="own_listaccess">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIST_ACCESS'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own[view]" id="own_view" value="1" <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['view']) && $this->form->config['own']['view'] ? ' checked="checked"' : ''; ?> /><label for="own_view">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VIEW'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own[new]" id="own_new" value="1" <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['new']) && $this->form->config['own']['new'] ? ' checked="checked"' : ''; ?> /><label for="own_new">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_NEW'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own[edit]" id="own_edit" value="1" <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['edit']) && $this->form->config['own']['edit'] ? ' checked="checked"' : ''; ?> /><label for="own_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_EDIT'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own[delete]" id="own_delete" value="1" <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['delete']) && $this->form->config['own']['delete'] ? ' checked="checked"' : ''; ?> /> <label for="own_delete">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_DELETE'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own[state]" id="own_state" value="1" <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['state']) && $this->form->config['own']['state'] ? ' checked="checked"' : ''; ?> /> <label for="own_state">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_STATE'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own[publish]" id="own_publish" value="1" <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['publish']) && $this->form->config['own']['publish'] ? ' checked="checked"' : ''; ?> /> <label for="own_publish">
                        <?php echo Text::_('PUBLISH'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own[fullarticle]" id="own_fullarticle"
                        value="1" <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['fullarticle']) && $this->form->config['own']['fullarticle'] ? ' checked="checked"' : ''; ?> /> <label for="own_fullarticle">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_FULL_ARTICLE'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own[language]" id="own_language" value="1"
                        <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['language']) && $this->form->config['own']['language'] ? ' checked="checked"' : ''; ?> /> <label for="own_language">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_CHANGE_LANGUAGE'); ?>
                    </label>
                    <input class="form-check-input" type="checkbox" name="own[rating]" id="own_rating" value="1" <?php echo isset($this->form->config['own']) && isset($this->form->config['own']) && isset($this->form->config['own']['rating']) && $this->form->config['own']['rating'] ? ' checked="checked"' : ''; ?> /> <label for="own_rating">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_RATING'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_GROUP') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIST_ACCESS') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VIEW') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_NEW') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_EDIT') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_DELETE') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_STATE') ?>
                    </th>
                    <th>
                        <?php echo Text::_('PUBLISH') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_FULL_ARTICLE') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_CHANGE_LANGUAGE') ?>
                    </th>
                    <th>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_RATING') ?>
                    </th>
                </tr>
            </thead>
            <tr class="<?php echo "row0"; ?>">
                <td style="background-color: #F0F0F0"></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="listaccess" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="view" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="new" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="edit" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="delete" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="state" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="publish" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="fullarticle" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="language" /></td>
                <td style="background-color: #F0F0F0"><input class="form-check-input" type="checkbox"
                        onclick="contentbuilder_selectAll(this,'be')" value="rating" /></td>
            </tr>
            <?php
            foreach ($this->gmap as $entry) {

                $k = 0;
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td>
                        <?php echo $entry->text; ?>
                    </td>
                    <td><input class="form-check-input" type="checkbox"
                            name="perms[<?php echo $entry->value; ?>][listaccess]" value="1" <?php echo !$this->form->id ? ' checked="checked"' : (isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['listaccess']) && $this->form->config['permissions'][$entry->value]['listaccess'] ? ' checked="checked"' : ''); ?> /></td>
                    <td><input class="form-check-input" type="checkbox" name="perms[<?php echo $entry->value; ?>][view]"
                            value="1" <?php echo !$this->form->id ? ' checked="checked"' : (isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['view']) && $this->form->config['permissions'][$entry->value]['view'] ? ' checked="checked"' : ''); ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox" name="perms[<?php echo $entry->value; ?>][new]"
                            value="1" <?php echo isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['new']) && $this->form->config['permissions'][$entry->value]['new'] ? ' checked="checked"' : ''; ?> /></td>
                    <td><input class="form-check-input" type="checkbox" name="perms[<?php echo $entry->value; ?>][edit]"
                            value="1" <?php echo isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['edit']) && $this->form->config['permissions'][$entry->value]['edit'] ? ' checked="checked"' : ''; ?> /></td>
                    <td><input class="form-check-input" type="checkbox" name="perms[<?php echo $entry->value; ?>][delete]"
                            value="1" <?php echo isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['delete']) && $this->form->config['permissions'][$entry->value]['delete'] ? ' checked="checked"' : ''; ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox" name="perms[<?php echo $entry->value; ?>][state]"
                            value="1" <?php echo isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['state']) && $this->form->config['permissions'][$entry->value]['state'] ? ' checked="checked"' : ''; ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox" name="perms[<?php echo $entry->value; ?>][publish]"
                            value="1" <?php echo isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['publish']) && $this->form->config['permissions'][$entry->value]['publish'] ? ' checked="checked"' : ''; ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox"
                            name="perms[<?php echo $entry->value; ?>][fullarticle]" value="1" <?php echo isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['fullarticle']) && $this->form->config['permissions'][$entry->value]['fullarticle'] ? ' checked="checked"' : ''; ?> /></td>
                    <td><input class="form-check-input" type="checkbox" name="perms[<?php echo $entry->value; ?>][language]"
                            value="1" <?php echo isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['language']) && $this->form->config['permissions'][$entry->value]['language'] ? ' checked="checked"' : ''; ?> />
                    </td>
                    <td><input class="form-check-input" type="checkbox" name="perms[<?php echo $entry->value; ?>][rating]"
                            value="1" <?php echo isset($this->form->config['permissions']) && isset($this->form->config['permissions'][$entry->value]) && isset($this->form->config['permissions'][$entry->value]['rating']) && $this->form->config['permissions'][$entry->value]['rating'] ? ' checked="checked"' : ''; ?> />
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
        </table>
        <?php

        echo $sliders->endPanel();
        */
        //FIN MODIF XDA - GILLES





        $title = Text::_('COM_CONTENTBUILDER_PERMISSIONS_USERS');
        echo $sliders->startPanel($title, "permtab2");
        ?>

        <table class="adminlist table table-striped">
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="limit_add">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIMIT_ADD'); ?>:
                    </label>
                </td>
                <td>
                    <input class="form-control form-control-sm w-100" id="limit_add" name="limit_add" type="text"
                        value="<?php echo $this->form->limit_add; ?>" />
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="limit_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_LIMIT_EDIT'); ?>:
                    </label>
                </td>
                <td>
                    <input class="form-control form-control-sm w-100" id="limit_edit" name="limit_edit" type="text"
                        value="<?php echo $this->form->limit_edit; ?>" />
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="verification_required_view">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VIEW'); ?>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="verification_required_view"
                        id="verification_required_view" value="1" <?php echo $this->form->verification_required_view ? ' checked="checked"' : '' ?> /><label for="verification_required_view">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_REQUIRED'); ?>
                    </label>
                    <input class="form-control form-control-sm" style="width: 50px;" id="verification_days_view"
                        name="verification_days_view" type="text"
                        value="<?php echo $this->form->verification_days_view; ?>" /> <label
                        for="verification_days_view">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_DAYS'); ?>
                    </label>
                    <input class="form-control form-control-sm" style="width: 300px;" id="verification_url_view"
                        name="verification_url_view" type="text"
                        value="<?php echo htmlentities($this->form->verification_url_view ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                    <label for="verification_url_view">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_URL'); ?>
                    </label>
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="verification_required_new">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_NEW'); ?>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="verification_required_new"
                        id="verification_required_new" value="1" <?php echo $this->form->verification_required_new ? ' checked="checked"' : '' ?> /><label for="verification_required_new">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_REQUIRED'); ?>
                    </label>
                    <input class="form-control form-control-sm" style="width: 50px;" id="verification_days_new"
                        name="verification_days_new" type="text"
                        value="<?php echo $this->form->verification_days_new; ?>" /> <label for="verification_days_new">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_DAYS'); ?>
                    </label>
                    <input class="form-control form-control-sm" style="width: 300px;" id="verification_url_new"
                        name="verification_url_new" type="text"
                        value="<?php echo htmlentities($this->form->verification_url_new ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                    <label for="verification_url_new">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_URL'); ?>
                    </label>
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label for="verification_required_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_EDIT'); ?>:
                    </label>
                </td>
                <td>
                    <input class="form-check-input" type="checkbox" name="verification_required_edit"
                        id="verification_required_edit" value="1" <?php echo $this->form->verification_required_edit ? ' checked="checked"' : '' ?> /><label for="verification_required_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_REQUIRED'); ?>
                    </label>
                    <input class="form-control form-control-sm" style="width: 50px;" id="verification_days_edit"
                        name="verification_days_edit" type="text"
                        value="<?php echo $this->form->verification_days_edit; ?>" /> <label
                        for="verification_days_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_DAYS'); ?>
                    </label>
                    <input class="form-control form-control-sm" style="width: 300px;" id="verification_url_new"
                        name="verification_url_edit" type="text"
                        value="<?php echo htmlentities($this->form->verification_url_edit ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                    <label for="verification_url_edit">
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_VERIFICATION_URL'); ?>
                    </label>
                </td>
            </tr>
            <tr class="row0">
                <td width="20%" align="right" class="key">
                    <label>
                        <?php echo Text::_('COM_CONTENTBUILDER_PERM_USERS'); ?>:
                    </label>
                </td>
                <td>
                    <?php echo '[<a href="index.php?option=com_contentbuilder&amp;controller=users&amp;tmpl=component&amp;form_id=' . $this->form->id . '" title="" data-bs-toggle="modal" data-bs-target="#edit-modal">' . Text::_('COM_CONTENTBUILDER_EDIT') . '</a>]'; ?>

                </td>
            </tr>
            <?php
            if (!$this->form->edit_by_type) {
                ?>
                <tr class="row0">
                    <td width="20%" align="right" class="key" valign="top">
                        <label for="act_as_registration">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_ACT_AS_REGISTRATION'); ?>:
                        </label>
                    </td>
                    <td>
                        <input class="form-check-input" type="checkbox" name="act_as_registration" id="act_as_registration"
                            value="1" <?php echo $this->form->act_as_registration ? ' checked="checked"' : '' ?> />
                        <br />
                        <br />
                        <select class="form-select-sm" name="registration_name_field" id="registration_name_field"
                            style="max-width: 200px;">
                            <option value=""> -
                                <?php echo Text::_('COM_CONTENTBUILDER_PERM_ACT_AS_REGISTRATION_NAME_FIELD'); ?> -
                            </option>
                            <?php
                            foreach ($this->elements as $the_element) {
                                ?>
                                <option value="<?php echo $the_element->reference_id; ?>" <?php echo $this->form->registration_name_field == $the_element->reference_id ? ' selected="selected"' : ''; ?>>
                                    <?php echo htmlentities($the_element->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </value>
                                    <?php
                            }
                            ?>
                        </select>
                        <br />
                        <br />
                        <select class="form-select-sm" name="registration_username_field" id="registration_username_field"
                            style="max-width: 200px;">
                            <option value=""> -
                                <?php echo Text::_('COM_CONTENTBUILDER_PERM_ACT_AS_REGISTRATION_USERNAME_FIELD'); ?> -
                            </option>
                            <?php
                            foreach ($this->elements as $the_element) {
                                ?>
                                <option value="<?php echo $the_element->reference_id; ?>" <?php echo $this->form->registration_username_field == $the_element->reference_id ? ' selected="selected"' : ''; ?>>
                                    <?php echo htmlentities($the_element->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </value>
                                    <?php
                            }
                            ?>
                        </select>
                        <br />
                        <br />
                        <select class="form-select-sm" name="registration_email_field" id="registration_email_field"
                            style="max-width: 200px;">
                            <option value=""> -
                                <?php echo Text::_('COM_CONTENTBUILDER_PERM_ACT_AS_REGISTRATION_EMAIL_FIELD'); ?> -
                            </option>
                            <?php
                            foreach ($this->elements as $the_element) {
                                ?>
                                <option value="<?php echo $the_element->reference_id; ?>" <?php echo $this->form->registration_email_field == $the_element->reference_id ? ' selected="selected"' : ''; ?>>
                                    <?php echo htmlentities($the_element->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </value>
                                    <?php
                            }
                            ?>
                        </select>
                        <br />
                        <br />
                        <select class="form-select-sm" name="registration_email_repeat_field"
                            id="registration_email_repeat_field" style="max-width: 200px;">
                            <option value=""> -
                                <?php echo Text::_('COM_CONTENTBUILDER_PERM_ACT_AS_REGISTRATION_EMAIL_REPEAT_FIELD'); ?> -
                            </option>
                            <?php
                            foreach ($this->elements as $the_element) {
                                ?>
                                <option value="<?php echo $the_element->reference_id; ?>" <?php echo $this->form->registration_email_repeat_field == $the_element->reference_id ? ' selected="selected"' : ''; ?>>
                                    <?php echo htmlentities($the_element->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </value>
                                    <?php
                            }
                            ?>
                        </select>
                        <br />
                        <br />
                        <select class="form-select-sm" name="registration_password_field" id="registration_password_field"
                            style="max-width: 200px;">
                            <option value=""> -
                                <?php echo Text::_('COM_CONTENTBUILDER_PERM_ACT_AS_REGISTRATION_PASSWORD_FIELD'); ?> -
                            </option>
                            <?php
                            foreach ($this->elements as $the_element) {
                                ?>
                                <option value="<?php echo $the_element->reference_id; ?>" <?php echo $this->form->registration_password_field == $the_element->reference_id ? ' selected="selected"' : ''; ?>>
                                    <?php echo htmlentities($the_element->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </value>
                                    <?php
                            }
                            ?>
                        </select>
                        <br />
                        <br />
                        <select class="form-select-sm" name="registration_password_repeat_field"
                            id="registration_password_repeat_field" style="max-width: 200px;">
                            <option value=""> -
                                <?php echo Text::_('COM_CONTENTBUILDER_PERM_ACT_AS_REGISTRATION_PASSWORD_REPEAT_FIELD'); ?>
                                -
                            </option>
                            <?php
                            foreach ($this->elements as $the_element) {
                                ?>
                                <option value="<?php echo $the_element->reference_id; ?>" <?php echo $this->form->registration_password_repeat_field == $the_element->reference_id ? ' selected="selected"' : ''; ?>>
                                    <?php echo htmlentities($the_element->label ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </value>
                                    <?php
                            }
                            ?>
                        </select>
                        <br />
                        <br />
                        <label for="force_login">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_FORCE_LOGIN'); ?>
                        </label>
                        <br />
                        <input class="form-check-input" type="checkbox" name="force_login" id="force_login" value="1" <?php echo $this->form->force_login ? ' checked="checked"' : '' ?> />
                        <br />
                        <br />
                        <label for="force_url">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_FORCE_URL'); ?>
                        </label>
                        <br />
                        <input class="form-control form-control-sm" id="force_url" name="force_url" type="text"
                            value="<?php echo htmlentities($this->form->force_url ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        <br />
                        <br />
                        <label for="registration_bypass_plugin">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_REGISTRATION_BYPASS_PLUGIN'); ?>
                        </label>
                        <br />
                        <select class="form-select-sm" name="registration_bypass_plugin" id="registration_bypass_plugin">
                            <option value=""> -
                                <?php echo Text::_('COM_CONTENTBUILDER_NONE'); ?> -
                            </option>
                            <?php
                            foreach ($this->verification_plugins as $registration_bypass_plugin) {
                                ?>
                                <option value="<?php echo $registration_bypass_plugin; ?>" <?php echo $registration_bypass_plugin == $this->form->registration_bypass_plugin ? ' selected="selected"' : ''; ?>>
                                    <?php echo $registration_bypass_plugin; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                        <br />
                        <br />
                        <label for="registration_bypass_verification_name">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_REGISTRATION_BYPASS_VERIFICATION_NAME'); ?>
                        </label>
                        <br />
                        <input class="form-control form-control-sm" type="text" name="registration_bypass_verification_name"
                            id="registration_bypass_verification_name"
                            value="<?php echo htmlentities($this->form->registration_bypass_verification_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        <br />
                        <br />
                        <label for="registration_bypass_verify_view">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_REGISTRATION_BYPASS_VERIFICATION_VIEW'); ?>
                        </label>
                        <br />
                        <input class="form-control form-control-sm" type="text" name="registration_bypass_verify_view"
                            id="registration_bypass_verify_view"
                            value="<?php echo htmlentities($this->form->registration_bypass_verify_view ?? '', ENT_QUOTES, 'UTF-8'); ?>" />

                        <br />
                        <br />
                        <label for="registration_bypass_plugin_params">
                            <?php echo Text::_('COM_CONTENTBUILDER_PERM_REGISTRATION_BYPASS_PLUGIN_PARAMS'); ?>
                        </label>
                        <br />
                        <textarea class="form-control form-control-sm" style="width: 100%;height: 80px;"
                            name="registration_bypass_plugin_params"
                            id="registration_bypass_plugin_params"><?php echo htmlentities($this->form->registration_bypass_plugin_params ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </td>
                </tr>
                <?php
            } else {
                ?>
                <input type="hidden" name="act_as_registration"
                    value="<?php echo htmlentities($this->form->act_as_registration ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_name_field"
                    value="<?php echo htmlentities($this->form->registration_name_field ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_username_field"
                    value="<?php echo htmlentities($this->form->registration_username_field ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_email_field"
                    value="<?php echo htmlentities($this->form->registration_email_field ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_email_repeat_field"
                    value="<?php echo htmlentities($this->form->registration_email_repeat_field ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_password_field"
                    value="<?php echo htmlentities($this->form->registration_password_field ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_password_repeat_field"
                    value="<?php echo htmlentities($this->form->registration_password_repeat_field ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_bypass_plugin"
                    value="<?php echo htmlentities($this->form->registration_bypass_plugin ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_bypass_verification_name"
                    value="<?php echo htmlentities($this->form->registration_bypass_verification_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_bypass_verify_view"
                    value="<?php echo htmlentities($this->form->registration_bypass_verify_view ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="registration_bypass_plugin_params"
                    value="<?php echo htmlentities($this->form->registration_bypass_plugin_params ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <?php
            }
            ?>
        </table>

        <?php
        echo $sliders->endPanel();

        echo $sliders->endPane();

        echo HTMLHelper::_('uitab.endTab');
        echo $cbcompat->endPane();
        ?>

    </div>

    <div class="clr"></div>

    <input type="hidden" name="option" value="com_contentbuilder" />
    <input type="hidden" name="id" value="<?php echo $this->form->id; ?>" />
    <input type="hidden" name="task" value="edit" />
    <input type="hidden" name="limitstart" value="" />
    <input type="hidden" name="controller" value="forms" />
    <input type="hidden" name="ordering" value="<?php echo $this->form->ordering; ?>" />
    <input type="hidden" name="published" value="<?php echo $this->form->published; ?>" />
    <input type="hidden" name="filter_order" value="" />
    <input type="hidden" name="filter_order_Dir" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="hidemainmenu" value="0" />
    <input type="hidden" name="tabStartOffset" value="<?php echo Factory::getApplication()->getSession()->get('tabStartOffset', 0); ?>" />
    <input type="hidden" name="slideStartOffset"
        value="<?php echo Factory::getApplication()->getSession()->get('slideStartOffset', 1); ?>" />
    <input type="hidden" name="email_users"
        value="<?php echo Factory::getApplication()->getSession()->get('email_users', 'none', 'com_contentbuilder'); ?>" />
    <input type="hidden" name="email_admins"
        value="<?php echo Factory::getApplication()->getSession()->get('email_admins', '', 'com_contentbuilder'); ?>" />

    <?php echo HTMLHelper::_('form.token'); ?>

</form>
<?php
$modalParams['title'] = Text::_('COM_CONTENTBUILDER_EDIT');
$modalParams['url'] = '#';
$modalParams['height'] = '400';
$modalParams['width'] = '800';
$modalParams['bodyHeight'] = 400;
$modalParams['modalWidth'] = 800;
echo HTMLHelper::_('bootstrap.renderModal', 'text-type-modal', $modalParams);

$modalParams['title'] = Text::_('COM_CONTENTBUILDER_EDIT');
$modalParams['url'] = '#';
$modalParams['height'] = '400';
$modalParams['width'] = '800';
$modalParams['bodyHeight'] = 400;
$modalParams['modalWidth'] = 800;
echo HTMLHelper::_('bootstrap.renderModal', 'edit-modal', $modalParams);

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('jquery');
?>

<script>
    let textTypeModal = document.getElementById('text-type-modal');
    textTypeModal.addEventListener('shown.bs.modal', function (event) {
        jQuery('.modal-body').css('display', 'none');
        jQuery('#text-type-modal').find('iframe').attr('src', event.relatedTarget.href);
        jQuery('.modal-body').css('display', 'flex');
    });

    let editModal = document.getElementById('edit-modal');
    editModal.addEventListener('shown.bs.modal', function (event) {
        console.log(event.relatedTarget.href);
        jQuery('.modal-body').css('display', 'none');
        jQuery('#edit-modal').find('iframe').attr('src', event.relatedTarget.href);
        jQuery('.modal-body').css('display', 'flex');
    });

    jQuery(document).ready(function () {

        setTimeout(function () {

            jQuery('joomla-tab button').on('click', function () {

                let item = localStorage.getItem('cb_clicked_view_tab');
                item = jQuery(this).index();
                localStorage.setItem('cb_clicked_view_tab', item);

                console.log('saved item: ', jQuery(this).index());
            });

        }, 500);

        let item = localStorage.getItem('cb_clicked_view_tab');

        if (item !== null) {
            console.log("loaded item: ", item);
            let element = jQuery('joomla-tab button').eq(item);
            jQuery(element).trigger('click');
            jQuery(element).trigger('blur');
        }
    });
</script>
