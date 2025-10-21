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
<form action="index.php?option=com_contentbuilder&controller=storages" method="post" name="adminForm" id="adminForm">

    <div id="editcell">
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
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_NAME'), 'name', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_STORAGE_TITLE'), 'title', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th width="120">
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_ORDERBY'), 'ordering', 'desc', @$this->lists['order']); ?>
                        <?php // TODO: dragandrop if ($this->ordering) echo HTMLHelper::_('grid.order',  $this->items );   ?>
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
                $link = Route::_('index.php?option=com_contentbuilder&controller=storages&task=edit&cid[]=' . $row->id);
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
                            <?php echo $row->title; ?>
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
                    <td colspan="8">
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
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="storages" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
