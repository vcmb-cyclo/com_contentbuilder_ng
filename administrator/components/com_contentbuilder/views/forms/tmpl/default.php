<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL 
 * @license     GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

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
Joomla.tableOrdering = function( order, dir, task ) {
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

            Joomla.submitbutton(task);
        }
        return false;
    }
    if (typeof Joomla != 'undefined') {
        Joomla.listItemTask = listItemTask;
    }
    //-->
</script>
<form action="index.php?option=com_contentbuilder&amp;controller=forms" method="post" name="adminForm" id="adminForm">

    <div id="editcell">
        <label for="filter_tag">
            <?php echo Text::_('COM_CONTENTBUILDER_FILTER_TAG'); ?>:
        </label>
        <select class="form-select-sm" id="filter_tag" name="filter_tag" onchange="document.adminForm.submit();">
            <option value=""> -
                <?php echo htmlentities(Text::_('COM_CONTENTBUILDER_FILTER_TAG_ALL'), ENT_QUOTES, 'UTF-8') ?> -
            </option>
            <?php
            foreach ($this->tags as $tag) {
                ?>
                <option value="<?php echo htmlentities($tag->tag, ENT_QUOTES, 'UTF-8') ?>" <?php echo strtolower($this->lists['filter_tag']) == strtolower($tag->tag) ? ' selected="selected"' : ''; ?>>
                    <?php echo htmlentities($tag->tag, ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php
            }
            ?>
        </select>
        <br />
        <br />
        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th width="5">
                        <?php echo Text::_('COM_CONTENTBUILDER_ID'); ?>
                    </th>
                    <th width="20">
                        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_VIEW_NAME'), 'name', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_TAG'), 'tag', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_FORM_SOURCE'), 'title', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_TYPE'), 'type', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_DISPLAY'), 'display_in', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th width="120">
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_ORDERBY'), 'ordering', 'desc', @$this->lists['order']); ?>
                        <?php // TODO: change to draganddrop if ($this->ordering) echo HTMLHelper::_('grid.order',  $this->items );   ?>
                    </th>
                    <th width="5">
                        <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED'); ?>
                    </th>
                </tr>
            </thead>
            <?php
            $k = 0;
            $n = count($this->items);
            for ($i = 0; $i < $n; $i++) {
                $row = $this->items[$i];
                $checked = HTMLHelper::_('grid.id', $i, $row->id);
                $link = Route::_('index.php?option=com_contentbuilder&controller=forms&task=edit&cid[]=' . $row->id);
                $published = contentbuilder_helpers::listPublish($row, $i);
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td>
                        <?php echo $row->id; ?>
                    </td>
                    <td>
                        <?php echo $checked; ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $row->name; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $row->tag; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $row->title; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $row->type; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $row->display_in == 0 ? Text::_('COM_CONTENTBUILDER_DISPLAY_FRONTEND') : ($row->display_in == 1 ? Text::_('COM_CONTENTBUILDER_DISPLAY_BACKEND') : Text::_('COM_CONTENTBUILDER_DISPLAY_BOTH')); ?>
                        </a>
                    </td>
                    <td class="order" nowrap="nowrap">
                        <span>
                            <?php echo $this->pagination->orderUpIcon($i, true, 'orderup', 'Move Up', $this->ordering); ?>
                        </span>
                        <span>
                            <?php echo $this->pagination->orderDownIcon($i, $n, true, 'orderdown', 'Move Down', $this->ordering); ?>
                        </span>
                        <?php $disabled = $this->ordering ? '' : 'disabled="disabled"'; ?>
                    </td>
                    <td>
                        <?php echo $published; ?>
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
            <tfoot>
                <tr>
                    <td colspan="9">
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
    </div>

    <input type="hidden" name="option" value="com_contentbuilder" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="limitstart" value="" />
    <input type="hidden" name="hidemainmenu" value="0" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="forms" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>