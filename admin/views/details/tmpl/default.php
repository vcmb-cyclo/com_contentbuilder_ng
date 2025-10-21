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


$edit_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('edit') : contentbuilder::authorize('edit');
$delete_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('delete') : contentbuilder::authorize('delete');
$view_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('view') : contentbuilder::authorize('view');
Factory::getApplication()->getDocument()->addScript(Uri::root(true) . '/components/com_contentbuilder/assets/js/contentbuilder.js');
?>

<?php if ($this->author)
    Factory::getApplication()->getDocument()->setMetaData('author', $this->author); ?>
<?php if ($this->robots)
    Factory::getApplication()->getDocument()->setMetaData('robots', $this->robots); ?>
<?php if ($this->rights)
    Factory::getApplication()->getDocument()->setMetaData('rights', $this->rights); ?>
<?php if ($this->metakey)
    Factory::getApplication()->getDocument()->setMetaData('keywords', $this->metakey); ?>
<?php if ($this->metadesc)
    Factory::getApplication()->getDocument()->setMetaData('description', $this->metadesc); ?>
<?php if ($this->xreference)
    Factory::getApplication()->getDocument()->setMetaData('xreference', $this->xreference); ?>

<?php Factory::getApplication()->getDocument()->addStyleDeclaration($this->theme_css); ?>
<?php Factory::getApplication()->getDocument()->addScriptDeclaration($this->theme_js); ?>
<script type="text/javascript">
<!--
function contentbuilder_delete(){
    var confirmed = confirm('<?php echo Text::_('COM_CONTENTBUILDER_CONFIRM_DELETE_MESSAGE'); ?>');
    if (confirmed) {
        location.href = '<?php echo 'index.php?option=com_contentbuilder&title=' . CBRequest::getVar('title', '') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&controller=edit&task=delete&view=edit&id=' . CBRequest::getInt('id', 0) . '&cid[]=' . CBRequest::getCmd('record_id', 0) . '&Itemid=' . CBRequest::getInt('Itemid', 0) . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order'); ?>';
    }
}
    //-->
</script>
<?php
if ($this->print_button):
    ?>
    <div class="hidden-phone cbPrintBar" style="float: right; text-align: right; padding-bottom: 5px;">
        <a
            href="javascript:window.open('<?php echo Route::_('index.php?option=com_contentbuilder&title=' . CBRequest::getVar('title', '') . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&controller=details&layout=print&tmpl=component&id=' . CBRequest::getInt('id', 0) . '&record_id=' . CBRequest::getCmd('record_id', 0)) ?>','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');void(0);"><i
                class="fa fa-print"></i></a>
    </div>
    <div style="clear: both;"></div>
    <?php
endif;
?>

<?php
if ($this->show_page_heading && $this->page_title) {
    ?>
    <h1 class="contentheading">
        <?php echo $this->page_title; ?>
    </h1>
    <?php
}
?>
<?php echo $this->event->afterDisplayTitle; ?>

<?php
ob_start();
?>

<?php
if ((CBRequest::getInt('cb_show_details_back_button', 1) && $this->show_back_button) || $delete_allowed || $edit_allowed) {
    ?>

    <div class="cbToolBar" style="float: right; text-align: right;">

        <?php
}
?>

    <?php
    if ($edit_allowed) {
        ?>
        <a class="btn btn-sm btn-primary cbButton cbEditButton"
            href="<?php echo Route::_('index.php?option=com_contentbuilder&controller=edit&id=' . CBRequest::getInt('id', 0) . '&record_id=' . CBRequest::getCmd('record_id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . '&Itemid=' . CBRequest::getInt('Itemid', 0) . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order')); ?>">
            <?php echo Text::_('COM_CONTENTBUILDER_EDIT') ?>
        </a>
        <?php
    }
    ?>
    <?php
    if ($delete_allowed) {
        ?>
        <button class="btn btn-sm btn-primary cbButton cbDeleteButton" onclick="contentbuilder_delete();">
            <?php echo Text::_('COM_CONTENTBUILDER_DELETE') ?>
        </button>
        <?php
    }
    ?>
    <?php if ($this->show_back_button && CBRequest::getBool('cb_show_details_back_button', 1)): ?>
        <a class="btn btn-sm btn-primary cbButton cbBackButton"
            href="<?php echo Route::_('index.php?option=com_contentbuilder&title=' . CBRequest::getVar('title', '') . '&controller=list&id=' . CBRequest::getInt('id', 0) . (CBRequest::getVar('tmpl', '') != '' ? '&tmpl=' . CBRequest::getVar('tmpl', '') : '') . (CBRequest::getVar('layout', '') != '' ? '&layout=' . CBRequest::getVar('layout', '') : '') . '&limitstart=' . CBRequest::getInt('limitstart', 0) . '&filter_order=' . CBRequest::getCmd('filter_order') . '&Itemid=' . CBRequest::getInt('Itemid', 0)); ?>">
            <?php echo Text::_('COM_CONTENTBUILDER_BACK') ?>
        </a>
    <?php endif; ?>

    <?php
    if ((CBRequest::getInt('cb_show_details_back_button', 1) && $this->show_back_button) || $delete_allowed || $edit_allowed) {
        ?>

    </div>

    <?php
    }
    ?>

<?php
$buttons = ob_get_contents();
ob_end_clean();

if (CBRequest::getInt('cb_show_details_top_bar', 1)) {
    ?>
    <div style="clear:right;"></div>
    <?php
    echo $buttons;
}
?>

<?php
if (CBRequest::getInt('cb_show_author', 1)) {
    ?>

    <?php if ($this->created): ?>
        <span class="small created-by">
            <?php echo Text::_('COM_CONTENTBUILDER_CREATED_ON'); ?>
            <?php echo HTMLHelper::_('date', $this->created, Text::_('DATE_FORMAT_LC2')); ?>
        </span>
    <?php endif; ?>

    <?php if ($this->created_by): ?>
        <span class="small created-by">
            <?php echo Text::_('COM_CONTENTBUILDER_BY'); ?>
            <?php echo $this->created_by; ?>
        </span><br />
    <?php endif; ?>

    <?php
}
?>

<?php
if (CBRequest::getInt('cb_show_details_top_bar', 1) && ((CBRequest::getInt('cb_show_details_back_button', 1) && $this->show_back_button) || $delete_allowed || $edit_allowed)) {
    ?>
    <br />
    <br />
    <?php
}
?>

<?php echo $this->event->beforeDisplayContent; ?>
<?php echo $this->toc ?>
<?php echo $this->tpl ?>
<?php echo $this->event->afterDisplayContent; ?>


<?php
if (CBRequest::getInt('cb_show_author', 1)) {
    ?>

    <?php if ($this->modified_by): ?>
        <br />

        <?php if ($this->modified): ?>
            <span class="small created-by">
                <?php echo Text::_('COM_CONTENTBUILDER_LAST_UPDATED_ON'); ?>
                <?php echo HTMLHelper::_('date', $this->modified, Text::_('DATE_FORMAT_LC2')); ?>
            </span>
        <?php endif; ?>

        <span class="small created-by">
            <?php echo Text::_('COM_CONTENTBUILDER_BY'); ?>
            <?php echo $this->modified_by; ?>
        </span>

    <?php endif; ?>

    <?php
}
?>

<br />

<?php
if (CBRequest::getInt('cb_show_details_bottom_bar', 1)) {
    echo $buttons;
    ?>
    <div style="clear:right;"></div>
    <?php
}
?>