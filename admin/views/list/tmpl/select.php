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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');

$language_allowed = contentbuilder::authorize('language');
$edit_allowed = contentbuilder::authorize('edit');
$delete_allowed = contentbuilder::authorize('delete');
$view_allowed = contentbuilder::authorize('view');
$new_allowed = contentbuilder::authorize('new');
$state_allowed = contentbuilder::authorize('state');
$publish_allowed = contentbuilder::authorize('publish');
$rating_allowed = contentbuilder::authorize('rating');

Factory::getApplication()->getDocument()->addScript(Uri::root(true) . '/components/com_contentbuilder/assets/js/contentbuilder.js');
?>
<?php Factory::getApplication()->getDocument()->addStyleDeclaration($this->theme_css); ?>
<?php Factory::getApplication()->getDocument()->addScriptDeclaration($this->theme_js); ?>
<script language="javascript" type="text/javascript">
    <!--
    function tableOrdering( order, dir, task ) {
        var form = document.adminForm;
            form.limitstart.value = <?php echo CBRequest::getInt('limitstart', 0) ?>;
    form.filter_order.value = order;
    form.filter_order_Dir.value = dir;
    document.adminForm.submit(task);
    }
    function contentbuilder_selectAll(checker) {
        for (var i = 0; i < document.adminForm.elements.length; i++) {
            if (document.adminForm.elements[i].name == 'cid[]') {
                if (checker.checked) {
                    document.adminForm.elements[i].checked = true;
                } else {
                    document.adminForm.elements[i].checked = false;
                }
            }
        }
    }
    function contentbuilder_state() {
        document.getElementById('controller').value = 'edit';
        document.getElementById('view').value = 'edit';
        document.getElementById('task').value = 'state';
        document.adminForm.submit();
    }
    function contentbuilder_publish() {
        document.getElementById('controller').value = 'edit';
        document.getElementById('view').value = 'edit';
        document.getElementById('task').value = 'publish';
        document.adminForm.submit();
    }
    function contentbuilder_language() {
        document.getElementById('controller').value = 'edit';
        document.getElementById('view').value = 'edit';
        document.getElementById('task').value = 'language';
        document.adminForm.submit();
    }
    function contentbuilder_delete() {
        var confirmed = confirm('<?php echo Text::_('COM_CONTENTBUILDER_CONFIRM_DELETE_MESSAGE'); ?>');
        if (confirmed) {
            document.getElementById('controller').value = 'edit';
            document.getElementById('view').value = 'edit';
            document.getElementById('task').value = 'delete';
            document.adminForm.submit();
        }
    }
    function contentbuilder_related_item(record_id) {
        window.parent.contentbuilder_related_item(record_id);
    }
    //-->
</script>
SELECT
<form action="index.php" method="get" name="adminForm" id="adminForm">
    <?php if ($this->page_title): ?>
        <h1 class="contentheading">
            <?php echo $this->page_title; ?>
            <?php
            if ($this->export_xls):
                ?>
                <span style="float: right; text-align: right;"><a
                        href="<?php echo Route::_('index.php?option=com_contentbuilder&controller=export&id=' . CBRequest::getInt('id', 0) . '&type=xls&format=raw&tmpl=component'); ?>">
                        <div class="
                cbXlsExportButton"
                            style="background-image: url(../components/com_contentbuilder/images/xls.png); background-repeat: no-repeat; width: 16px; height: 16px;"
                            alt="Export"></div>
                    </a></span>
                <?php
            endif;
            ?>
        </h1>
    <?php endif; ?>
    <?php echo $this->intro_text; ?>
    <div id="editcell">
        <table class="cbFilterTable" width="100%">
            <tr>
                <td>

                </td>
                <td align="right" width="40%" nowrap="nowrap">
                    <?php
                    if ($new_allowed) {
                        ?>
                        <a class="button cbButton cbNewButton"
                            href="<?php echo Route::_('index.php?option=com_contentbuilder&controller=edit&backtolist=1&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&record_id=&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order')); ?>">
                            <?php echo Text::_('COM_CONTENTBUILDER_NEW'); ?>
                        </a>
                        <?php
                    }
                    ?>
                    <?php if ($new_allowed && $delete_allowed) { ?>|
                    <?php } ?>
                    <?php
                    if ($delete_allowed) {
                        ?>
                        <a class=" button cbButton cbDeleteButton" href="javascript:contentbuilder_delete();">
                            <?php echo Text::_('COM_CONTENTBUILDER_DELETE'); ?>
                        </a>
                        <?php
                    }
                    ?>
                    <?php if (($new_allowed || $delete_allowed) && $state_allowed) { ?>|
                    <?php } ?>
                    <?php
                    if ($state_allowed && count($this->states)) {
                        ?>
                        <select style="max-width: 100px;" name="list_state">
                            <option value="0"> -
                                <?php echo Text::_('COM_CONTENTBUILDER_EDIT_STATE'); ?> -
                            </option>
                            <?php
                            foreach ($this->states as $state) {
                                ?>
                                <option value="<?php echo $state['id'] ?>">
                                    <?php echo $state['title'] ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                        <a class=" button cbButton cbSetButton" href="javascript:contentbuilder_state();">
                            <?php echo Text::_('COM_CONTENTBUILDER_SET'); ?>
                        </a>
                        <?php
                    }
                    ?>
                    <?php if (($new_allowed || $delete_allowed || $state_allowed) && $publish_allowed) { ?>|
                    <?php } ?>
                    <?php
                    if ($publish_allowed) {
                        ?>
                        <select style="max-width: 100px;" name="list_publish">
                            <option value="-1"> -
                                <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED_UNPUBLISHED'); ?> -
                            </option>
                            <option value="1">
                                <?php echo Text::_('PUBLISH') ?>
                            </option>
                            <option value="0">
                                <?php echo Text::_('UNPUBLISH') ?>
                            </option>
                        </select>
                        <a class="button cbButton cbSetButton" href="javascript:contentbuilder_publish();">
                            <?php echo Text::_('COM_CONTENTBUILDER_SET'); ?>
                        </a>
                        <?php
                    }
                    ?>
                    <?php if (($new_allowed || $delete_allowed || $state_allowed || $publish_allowed) && $language_allowed) { ?>|
                    <?php } ?>
                    <?php
                    if ($language_allowed) {
                        ?>
                        <select style="max-width: 100px;" name="list_language">
                            <option value="*"> -
                                <?php echo Text::_('COM_CONTENTBUILDER_LANGUAGE'); ?> -
                            </option>
                            <option value="*">
                                <?php echo Text::_('COM_CONTENTBUILDER_ANY'); ?>
                            </option>
                            <?php
                            foreach ($this->languages as $filter_language) {
                                ?>
                                <option value="<?php echo $filter_language; ?>">
                                    <?php echo $filter_language; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                        <a class=" button cbButton cbSetButton" href="javascript:contentbuilder_language();">
                            <?php echo Text::_('COM_CONTENTBUILDER_SET'); ?>
                        </a>
                        <?php
                    }

                    if ($this->show_records_per_page) {
                        ?>

                        <?php echo $this->pagination->getPagesCounter(); ?>
                        <?php
                        echo '&nbsp;&nbsp;&nbsp;' . Text::_('COM_CONTENTBUILDER_DISPLAY_NUM') . '&nbsp;';
                        echo $this->pagination->getLimitBox();
                        ?>
                        <?php echo Text::_('COM_CONTENTBUILDER_OF'); ?>
                        <?php echo $this->total; ?>

                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <?php
                if ($this->display_filter) {
                    ?>
                    <td align="left" width="60%" nowrap="nowrap">
                        <?php echo Text::_('COM_CONTENTBUILDER_FILTER') . '&nbsp;'; ?>
                        <input type="text" id="contentbuilder_filter" name="filter"
                            value="<?php echo $this->escape($this->lists['filter']); ?>" class=" inputbox"
                            onchange="document.adminForm.submit();" />
                        <?php
                        if ($this->list_state && count($this->states)) {
                            ?>
                            <select style="max-width: 100px;" name="list_state_filter" id="list_state_filter"
                                onchange="document.adminForm.submit();">
                                <option value="0"> -
                                    <?php echo Text::_('COM_CONTENTBUILDER_EDIT_STATE'); ?> -
                                </option>
                                <?php
                                foreach ($this->states as $state) {
                                    ?>
                                    <option value="<?php echo $state['id'] ?>" <?php echo $this->lists['filter_state'] == $state['id'] ? ' selected="selected"' : ''; ?>>
                                        <?php echo $state['title'] ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <?php
                        }

                        if ($this->list_publish && $publish_allowed) {
                            ?>

                            <select style=" max-width: 100px;" name="list_publish_filter" id="list_publish_filter"
                                onchange="document.adminForm.submit();">
                                <option value="-1"> -
                                    <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED_UNPUBLISHED'); ?> -
                                </option>
                                <option value="1" <?php echo $this->lists['filter_publish'] == 1 ? ' selected="selected"' : ''; ?>>
                                    <?php echo Text::_('PUBLISHED') ?>
                                </option>
                                <option value="0" <?php echo $this->lists['filter_publish'] == 0 ? ' selected="selected"' : ''; ?>>
                                    <?php echo Text::_('UNPUBLISHED') ?>
                                </option>
                            </select>
                            <?php
                        }

                        if ($this->list_language) {
                            ?>
                            <select style="max-width: 100px;" name="list_language_filter" id="list_language_filter"
                                onchange="document.adminForm.submit();">
                                <option value=""> -
                                    <?php echo Text::_('COM_CONTENTBUILDER_LANGUAGE'); ?> -
                                </option>
                                <?php
                                foreach ($this->languages as $filter_language) {
                                    ?>
                                    <option value="<?php echo $filter_language; ?>" <?php echo $this->lists['filter_language'] == $filter_language ? ' selected="selected"' : ''; ?>>
                                        <?php echo $filter_language; ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <?php
                        }
                        ?>
                        <button class=" button cbButton cbSearchButton" onclick="document.adminForm.submit();">
                            <?php echo Text::_('COM_CONTENTBUILDER_SEARCH') ?>
                        </button>
                        <button class="button cbButton cbResetButton"
                            onclick="document.getElementById('contentbuilder_filter').value='';<?php echo $this->list_state && count($this->states) ? "if(document.getElementById('list_state_filter')) document.getElementById('list_state_filter').selectedIndex=0;" : ""; ?><?php echo $this->list_publish ? "if(document.getElementById('list_publish_filter')) document.getElementById('list_publish_filter').selectedIndex=0;" : ""; ?>document.adminForm.submit();">
                            <?php echo Text::_('COM_CONTENTBUILDER_RESET') ?>
                        </button>
                    </td>
                    <?php
                }
                ?>
                <td>

                </td>
            </tr>
        </table>
        <table class="adminlist">
            <thead>
                <tr>
                    <?php
                    if ($this->show_id_column) {
                        ?>
                        <th class="sectiontableheader" width="5">
                            <?php echo HTMLHelper::_('grid.sort', htmlentities('COM_CONTENTBUILDER_ID', ENT_QUOTES, 'UTF-8'), 'colRecord', $this->lists['order_Dir'], $this->lists['order']); ?>
                        </th>
                        <?php
                    }

                    if ($this->select_column && ($delete_allowed || $state_allowed || $publish_allowed)) {
                        ?>
                        <th class="sectiontableheader" width="20">
                            <input class="contentbuilder_select_all" type="checkbox"
                                onclick="contentbuilder_selectAll(this);" />
                        </th>
                        <?php
                    }
                    ?>
                    <th class="sectiontableheader" width="20">
                        <?php echo Text::_('COM_CONTENTBUILDER_ADD_RELATION'); ?>
                    </th>
                    <?php
                    if ($this->edit_button && $edit_allowed) {
                        ?>
                        <th class="sectiontableheader" width="20">
                            <?php echo Text::_('COM_CONTENTBUILDER_EDIT'); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_state) {
                        ?>
                        <th class="sectiontableheader" width="20">
                            <?php echo Text::_('COM_CONTENTBUILDER_EDIT_STATE'); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_publish && $publish_allowed) {
                        ?>
                        <th class="sectiontableheader" width="20">
                            <?php echo Text::_('PUBLISHED'); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_language) {
                        ?>
                        <th class="sectiontableheader" width="20">
                            <?php echo Text::_('COM_CONTENTBUILDER_LANGUAGE'); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_article) {
                        ?>
                        <th class="sectiontableheader" width="20">
                            <?php echo HTMLHelper::_('grid.sort', htmlentities('COM_CONTENTBUILDER_ARTICLE', ENT_QUOTES, 'UTF-8'), 'colArticleId', $this->lists['order_Dir'], $this->lists['order']); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_author) {
                        ?>
                        <th class="sectiontableheader" width="20">
                            <?php echo HTMLHelper::_('grid.sort', htmlentities('COM_CONTENTBUILDER_AUTHOR', ENT_QUOTES, 'UTF-8'), 'colAuthor', $this->lists['order_Dir'], $this->lists['order']); ?>
                        </th>
                        <?php
                    }

                    if ($this->list_rating) {
                        ?>
                        <th class="sectiontableheader" width="20">
                            <?php echo HTMLHelper::_('grid.sort', htmlentities('COM_CONTENTBUILDER_RATING', ENT_QUOTES, 'UTF-8'), 'colRating', $this->lists['order_Dir'], $this->lists['order']); ?>
                        </th>
                        <?php
                    }

                    if ($this->labels) {
                        foreach ($this->labels as $reference_id => $label) {
                            ?>
                            <th class="sectiontableheader">
                                <?php echo HTMLHelper::_('grid.sort', nl2br(htmlentities(contentbuilder_wordwrap($label, 20, "\n", true), ENT_QUOTES, 'UTF-8')), "col$reference_id", $this->lists['order_Dir'], $this->lists['order']); ?>
                            </th>
                            <?php
                        }
                    }
                    ?>
                </tr>
            </thead>
            <?php
            $k = 0;
            $n = count($this->items);
            for ($i = 0; $i < $n; $i++) {
                $row = $this->items[$i];
                $link = Route::_('index.php?option=com_contentbuilder&layout=select&&controller=details&id=' . $this->form_id . '&record_id=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
                $edit_link = Route::_('index.php?option=com_contentbuilder&layout=select&controller=edit&backtolist=1&id=' . $this->form_id . '&record_id=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
                $publish_link = Route::_('index.php?option=com_contentbuilder&layout=select&controller=edit&task=publish&backtolist=1&id=' . $this->form_id . '&list_publish=1&cid[]=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
                $unpublish_link = Route::_('index.php?option=com_contentbuilder&layout=select&controller=edit&task=publish&backtolist=1&id=' . $this->form_id . '&list_publish=0&cid[]=' . $row->colRecord . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'));
                $select = '<input type="checkbox" name="cid[]" value="' . $row->colRecord . '"/>';
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <?php
                    if ($this->show_id_column) {
                        ?>
                        <td>
                            <?php
                            if (($view_allowed || $this->own_only)) {
                                ?>
                                <a href=" <?php echo $link; ?>">
                                    <?php echo $row->colRecord; ?>
                                </a>
                                <?php
                            } else {
                                ?>
                                <?php echo $row->colRecord; ?>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->select_column && ($delete_allowed || $state_allowed || $publish_allowed)) {
                        ?>
                        <td>
                            <?php echo $select; ?>
                        </td>
                        <?php
                    }
                    ?>

                    <td>
                        <a
                            href="javascript:contentbuilder_related_item(<?php echo $row->colRecord; ?>);window.parent.SqueezeBox.close();"><img
                                src="../components/com_contentbuilder/images/plus.png" border="0" width="18"
                                height="18" /></a>
                    </td>

                    <?php
                    if ($this->edit_button && $edit_allowed) {
                        ?>
                        <td>
                            <a href="<?php echo $edit_link; ?>"><img src=" ../components/com_contentbuilder/images/edit.png"
                                    border="0" width="18" height="18" /></a>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_state) {
                        ?>
                        <td
                            style="background-color: #<?php echo isset($this->state_colors[$row->colRecord]) ? $this->state_colors[$row->colRecord] : 'FFFFFF'; ?>;">
                            <?php echo isset($this->state_titles[$row->colRecord]) ? htmlentities($this->state_titles[$row->colRecord], ENT_QUOTES, 'UTF-8') : ''; ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_publish && $publish_allowed) {
                        ?>
                        <td align="center" valign="middle">
                            <?php echo contentbuilder_helpers::publishButton(isset($this->published_items[$row->colRecord]) && $this->published_items[$row->colRecord] ? true : false, $publish_link, $unpublish_link, 'tick.png', 'publish_x.png', $publish_allowed); ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_language) {
                        ?>
                        <td>
                            <?php echo isset($this->lang_codes[$row->colRecord]) && $this->lang_codes[$row->colRecord] ? $this->lang_codes[$row->colRecord] : '*'; ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_article) {
                        ?>
                        <td>
                            <?php
                            if (($view_allowed || $this->own_only)) {
                                ?>
                                <a href="<?php echo $link; ?>">
                                    <?php echo $row->colArticleId; ?>
                                </a>
                                <?php
                            } else {
                                ?>
                                <?php echo $row->colArticleId; ?>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_author) {
                        ?>
                        <td>
                            <?php
                            if (($view_allowed || $this->own_only)) {
                                ?>
                                <a href=" <?php echo $link; ?>">
                                    <?php echo htmlentities($row->colAuthor, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                                <?php
                            } else {
                                ?>
                                <?php echo htmlentities($row->colAuthor, ENT_QUOTES, 'UTF-8'); ?>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    if ($this->list_rating) {
                        ?>
                        <td>
                            <?php
                            echo contentbuilder::getRating(CBRequest::getInt('id', 0), $row->colRecord, $row->colRating, $this->rating_slots, CBRequest::getCmd('lang', ''), $rating_allowed, $row->colRatingCount, $row->colRatingSum);
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <?php
                    foreach ($row as $key => $value) {
                        // filtering out disallowed columns
                        if (in_array(str_replace('col', '', $key), $this->visible_cols)) {
                            ?>
                            <td>
                                <?php
                                if (in_array(str_replace('col', '', $key), $this->linkable_elements) && ($view_allowed || $this->own_only)) {
                                    ?>
                                    <a href="<?php echo $link; ?>">
                                        <?php echo $value; ?>
                                    </a>
                                    <?php
                                } else {
                                    ?>
                                    <?php echo $value; ?>
                                    <?php
                                }
                                ?>
                            </td>
                            <?php
                        }
                    }
                    ?>
                </tr>
                <?php
                $k = 1 - $k;
            }
            $pages_links = $this->pagination->getPagesLinks();
            if ($pages_links) {
                ?>
                <tfoot>
                    <tr>
                        <td colspan="1000" valign="middle" align="center">
                            <div class="pagination">
                                <?php echo $pages_links; ?>
                            </div>
                        </td>
                    </tr>
                </tfoot>
                <?php
            }
            ?>
        </table>
    </div>
    <?php
    if (CBRequest::getVar('tmpl', '') != '') {
        ?>
        <input type="hidden" name="tmpl" value="<?php echo CBRequest::getVar('tmpl', ''); ?>" />
        <?php
    }
    ?>
    <input type="hidden" name="option" value="com_contentbuilder" />
    <input type="hidden" name="task" id="task" value="" />
    <input type="hidden" name="controller" id="controller" value="list" />
    <input type="hidden" name="view" id="view" value="list" />
    <input type="hidden" name="layout" id="view" value="select" />
    <input type="hidden" name="Itemid" value="<?php echo CBRequest::getInt('Itemid', 0); ?>" />
    <input type="hidden" name="limitstart" value="" />
    <input type="hidden" name="id" value="<?php echo CBRequest::getInt('id', 0) ?>" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>