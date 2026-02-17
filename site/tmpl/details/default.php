<?php

/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderLegacyHelper;

$frontend = Factory::getApplication()->isClient('site');
$edit_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('edit') : ContentbuilderLegacyHelper::authorize('edit');
$delete_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('delete') : ContentbuilderLegacyHelper::authorize('delete');
$view_allowed = $frontend ? ContentbuilderLegacyHelper::authorizeFe('view') : ContentbuilderLegacyHelper::authorize('view');

$input = Factory::getApplication()->input;
$list = (array) $input->get('list', [], 'array');
$listStart = isset($list['start']) ? $input->getInt('list[start]', 0) : 0;
$listLimit = isset($list['limit']) ? $input->getInt('list[limit]', 0) : 0;
if ($listLimit === 0) {
    $listLimit = (int) Factory::getApplication()->get('list_limit');
}
$listOrdering = isset($list['ordering']) ? $input->getCmd('list[ordering]', '') : '';
$listDirection = isset($list['direction']) ? $input->getCmd('list[direction]', '') : '';
$listQuery = http_build_query(['list' => [
    'start' => $listStart,
    'limit' => $listLimit,
    'ordering' => $listOrdering,
    'direction' => $listDirection,
]]);
$previewQuery = '';
$previewEnabled = $input->getBool('cb_preview', false);
$previewUntil = $input->getInt('cb_preview_until', 0);
$previewSig = (string) $input->getString('cb_preview_sig', '');
$previewActorId = $input->getInt('cb_preview_actor_id', 0);
$previewActorName = (string) $input->getString('cb_preview_actor_name', '');
$isAdminPreview = $input->getBool('cb_preview_ok', false);
$adminReturnUrl = Uri::root() . 'administrator/index.php?option=com_contentbuilder_ng&task=form.edit&id=' . (int) $input->getInt('id', 0);
if ($previewEnabled && $previewUntil > 0 && $previewSig !== '') {
    $previewQuery = '&cb_preview=1'
        . '&cb_preview_until=' . $previewUntil
        . '&cb_preview_actor_id=' . (int) $previewActorId
        . '&cb_preview_actor_name=' . rawurlencode($previewActorName)
        . '&cb_preview_sig=' . rawurlencode($previewSig);
}

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

// Charge le manifeste joomla.asset.json du composant
$wa->getRegistry()->addExtensionRegistryFile('com_contentbuilder_ng');

$wa->useScript('jquery');
$wa->useScript('com_contentbuilder_ng.contentbuilder_ng');
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
    function contentbuilder_ng_delete() {
        var confirmed = confirm('<?php echo Text::_('COM_CONTENTBUILDER_NG_CONFIRM_DELETE_MESSAGE'); ?>');
        if (confirmed) {
            location.href = '<?php echo 'index.php?option=com_contentbuilder_ng&title=' . Factory::getApplication()->input->get('title', '', 'string') . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . '&task=detail.delete&task=edit.display&id=' . Factory::getApplication()->input->getInt('id', 0) . '&cid[]=' . Factory::getApplication()->input->getCmd('record_id', 0) . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0) . ($listQuery !== '' ? '&' . $listQuery : ''); ?>';
        }
    }
    //
    -->
</script>
<?php
if ($this->print_button):
?>
    <div class="hidden-phone cbPrintBar d-flex justify-content-end mb-2">
        <a
            class="btn btn-sm btn-outline-secondary"
            href="javascript:window.open('<?php echo Route::_('index.php?option=com_contentbuilder_ng&title=' . Factory::getApplication()->input->get('title', '', 'string') . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . '&task=details.display&layout=print&tmpl=component&id=' . Factory::getApplication()->input->getInt('id', 0) . '&record_id=' . Factory::getApplication()->input->getCmd('record_id', 0) . $previewQuery) ?>','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');void(0);"><i
                class="fa fa-print" aria-hidden="true"></i> <?php echo Text::_('JGLOBAL_PRINT'); ?></a>
    </div>
<?php
endif;
?>

<div class="cbDetailsWrapper">

<?php if ($isAdminPreview): ?>
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <span><?php echo Text::_('COM_CONTENTBUILDER_NG_PREVIEW_MODE'); ?></span>
        <a class="btn btn-sm btn-outline-secondary" href="<?php echo $adminReturnUrl; ?>">
            <?php echo Text::_('COM_CONTENTBUILDER_NG_BACK_TO_ADMIN'); ?>
        </a>
    </div>
<?php endif; ?>

<?php
if ($this->show_page_heading && $this->page_title) {
?>
    <h1 class="display-6 mb-4">
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
if ((Factory::getApplication()->input->getInt('cb_show_details_back_button', 1) && $this->show_back_button) || $delete_allowed || $edit_allowed) {
?>

    <div class="cbToolBar d-flex justify-content-end gap-2 flex-wrap mb-3">
    <?php
}
    ?>

    <?php if ($edit_allowed) { ?>
        <a class="btn btn-sm btn-primary cbButton cbEditButton"
            href="<?php echo Route::_('index.php?option=com_contentbuilder_ng&task=edit.display&id=' . Factory::getApplication()->input->getInt('id', 0) . '&record_id=' . Factory::getApplication()->input->getCmd('record_id', 0) . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0) . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . ($listQuery !== '' ? '&' . $listQuery : '') . $previewQuery); ?>"
            title="<?php echo Text::_('COM_CONTENTBUILDER_NG_EDIT'); ?>">
            <?php echo Text::_('COM_CONTENTBUILDER_NG_EDIT') ?>
        </a>
    <?php
    }
    ?>
    <?php if ($delete_allowed) { ?>
        <button class="btn btn-sm btn-primary cbButton cbDeleteButton" onclick="contentbuilder_ng_delete();"
            title="<?php echo Text::_('COM_CONTENTBUILDER_NG_DELETE'); ?>">
            <i class="fa fa-trash" aria-hidden="true"></i>
            <?php echo Text::_('COM_CONTENTBUILDER_NG_DELETE') ?>
        </button>
    <?php
    }
    ?>
    <?php if ($this->show_back_button && Factory::getApplication()->input->getBool('cb_show_details_back_button', 1)): ?>
        <a class="btn btn-sm btn-outline-secondary cbButton cbBackButton"
            href="<?php echo Route::_('index.php?option=com_contentbuilder_ng&title=' . Factory::getApplication()->input->get('title', '', 'string') . '&task=list.display&id=' . Factory::getApplication()->input->getInt('id', 0) . (Factory::getApplication()->input->get('tmpl', '', 'string') != '' ? '&tmpl=' . Factory::getApplication()->input->get('tmpl', '', 'string') : '') . (Factory::getApplication()->input->get('layout', '', 'string') != '' ? '&layout=' . Factory::getApplication()->input->get('layout', '', 'string') : '') . ($listQuery !== '' ? '&' . $listQuery : '') . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0) . $previewQuery); ?>"
            title="<?php echo Text::_('COM_CONTENTBUILDER_NG_BACK'); ?>">
            <span class="icon-arrow-left me-1" aria-hidden="true"></span>
            <?php echo Text::_('COM_CONTENTBUILDER_NG_BACK') ?>
        </a>
    <?php endif; ?>

    <?php
    if ((Factory::getApplication()->input->getInt('cb_show_details_back_button', 1) && $this->show_back_button) || $delete_allowed || $edit_allowed) {
    ?>

    </div>

<?php
    }
?>

<?php
$buttons = ob_get_contents();
ob_end_clean();

if (Factory::getApplication()->input->getInt('cb_show_details_top_bar', 1)) {
?>
    <div style="clear:right;"></div>
<?php
    echo $buttons;
}
?>

<?php
if (Factory::getApplication()->input->getInt('cb_show_author', 1)) {
?>

    <?php if ($this->created): ?>
        <span class="small created-by">
            <?php echo Text::_('COM_CONTENTBUILDER_NG_CREATED_ON'); ?>
            <?php echo HTMLHelper::_('date', $this->created, Text::_('DATE_FORMAT_LC5')); ?>
        </span>
    <?php endif; ?>

    <?php if ($this->created_by): ?>
        <span class="small created-by">
            <?php echo Text::_('COM_CONTENTBUILDER_NG_BY'); ?>
            <?php echo $this->created_by; ?>
        </span><br />
    <?php endif; ?>

<?php
}
?>

<?php
if (Factory::getApplication()->input->getInt('cb_show_details_top_bar', 1) && ((Factory::getApplication()->input->getInt('cb_show_details_back_button', 1) && $this->show_back_button) || $delete_allowed || $edit_allowed)) {
?>
    <br />
    <br />
<?php
}
?>

<div class="cbDetailsBody">
<?php echo $this->event->beforeDisplayContent; ?>
<?php echo $this->toc ?>
<?php echo $this->tpl ?>
<?php echo $this->event->afterDisplayContent; ?>
</div>


<?php
if (Factory::getApplication()->input->getInt('cb_show_author', 1)) {
?>

    <?php if ($this->modified_by): ?>
        <br />

        <?php if ($this->modified): ?>
            <span class="small created-by">
                <?php echo Text::_('COM_CONTENTBUILDER_NG_LAST_UPDATED_ON'); ?>
                <?php echo HTMLHelper::_('date', $this->modified, Text::_('DATE_FORMAT_LC5')); ?>
            </span>
        <?php endif; ?>

        <span class="small created-by">
            <?php echo Text::_('COM_CONTENTBUILDER_NG_BY'); ?>
            <?php echo $this->modified_by; ?>
        </span>

    <?php endif; ?>

<?php
}
?>

<br />

<?php
if (Factory::getApplication()->input->getInt('cb_show_details_bottom_bar', 1)) {
    echo $buttons;
?>
    <div style="clear:right;"></div>
<?php
}
?>

</div>
