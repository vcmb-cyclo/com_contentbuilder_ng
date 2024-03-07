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
    listItemTask = Joomla.listItemTask;
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm">


    <input style="display:inline-block;" class="form-control form-control-sm w-25" type="text" name="users_search"
        id="users_search" value="<?php echo $this->lists['users_search']; ?>" onchange="document.adminForm.submit();" />
    <input type="button" class="btn btn-sm btn-primary" value="<?php echo Text::_('COM_CONTENTBUILDER_SEARCH'); ?>"
        onclick="this.form.submit();" />
    <input type="button" class="btn btn-sm btn-primary" value="<?php echo Text::_('COM_CONTENTBUILDER_RESET'); ?>"
        onclick="document.getElementById('users_search').value='';document.adminForm.submit();" />


    <div style="float:right">
        <select class="form-select-sm"
            onchange="if(this.selectedIndex == 1 || this.selectedIndex == 2){document.adminForm.task.value=this.options[this.selectedIndex].value;document.adminForm.submit();}">
            <option> -
                <?php echo Text::_('COM_CONTENTBUILDER_PUBLISHED_UNPUBLISHED'); ?> -
            </option>
            <option value="publish">
                <?php echo Text::_('PUBLISH'); ?>
            </option>
            <option value="unpublish">
                <?php echo Text::_('UNPUBLISH'); ?>
            </option>
        </select>
        <select class="form-select-sm"
            onchange="if(this.selectedIndex != 0){document.adminForm.task.value=this.options[this.selectedIndex].value;document.adminForm.submit();}">
            <option> -
                <?php echo Text::_('COM_CONTENTBUILDER_SET_VERIFICATION'); ?> -
            </option>
            <option value="verified_view">
                <?php echo Text::_('COM_CONTENTBUILDER_VERIFIED_VIEW'); ?>
            </option>
            <option value="not_verified_view">
                <?php echo Text::_('COM_CONTENTBUILDER_UNVERIFIED_VIEW'); ?>
            </option>
            <option value="verified_new">
                <?php echo Text::_('COM_CONTENTBUILDER_VERIFIED_NEW'); ?>
            </option>
            <option value="not_verified_new">
                <?php echo Text::_('COM_CONTENTBUILDER_UNVERIFIED_NEW'); ?>
            </option>
            <option value="verified_edit">
                <?php echo Text::_('COM_CONTENTBUILDER_VERIFIED_EDIT'); ?>
            </option>
            <option value="not_verified_edit">
                <?php echo Text::_('COM_CONTENTBUILDER_UNVERIFIED_EDIT'); ?>
            </option>
        </select>
    </div>

    <div style="clear:both;"></div>

    <div id="editcell">
        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th width="5">
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_ID'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th width="20">
                        <input class="form-check-input" type="checkbox" name="toggle" value=""
                            onclick="Joomla.checkAll(this);" />
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_NAME'), 'name', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_USERNAME'), 'username', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_VERIFIED_VIEW'), 'verified_view', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_VERIFIED_NEW'), 'verified_new', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_VERIFIED_EDIT'), 'verified_edit', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                    <th width="5">
                        <?php echo HTMLHelper::_('grid.sort', Text::_('COM_CONTENTBUILDER_PUBLISHED'), 'published', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                </tr>
            </thead>
            <?php
            $k = 0;
            $n = count($this->items);
            for ($i = 0; $i < $n; $i++) {
                $row = $this->items[$i];
                $checked = HTMLHelper::_('grid.id', $i, $row->id);
                $link = Route::_('index.php?option=com_contentbuilder&controller=users&tmpl=' . CBRequest::getCmd('tmpl', '') . '&task=edit&form_id=' . CBRequest::getInt('form_id', 0) . '&joomla_userid=' . $row->id);
                if ($row->published === null) {
                    $row->published = 1;
                }
                $published = contentbuilder_helpers::listPublish($row, $i);
                $verified_view = contentbuilder_helpers::listVerifiedView($row, $i);
                $verified_new = contentbuilder_helpers::listVerifiedNew($row, $i);
                $verified_edit = contentbuilder_helpers::listVerifiedEdit($row, $i);
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
                            <?php echo $row->username; ?>
                        </a>
                    </td>
                    <td>
                        <?php echo $verified_view; ?>
                    </td>
                    <td>
                        <?php echo $verified_new; ?>
                    </td>
                    <td>
                        <?php echo $verified_edit; ?>
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
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>

        </table>
    </div>

    <input type="hidden" name="option" value="com_contentbuilder" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="users" />
    <input type="hidden" name="form_id" value="<?php echo CBRequest::getInt('form_id', 0); ?>" />
    <input type="hidden" name="tmpl" value="<?php echo CBRequest::getWord('tmpl', ''); ?>" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>