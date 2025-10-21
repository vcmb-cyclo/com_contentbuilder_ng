<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$new_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('new') : contentbuilder::authorize('new');
$edit_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('edit') : contentbuilder::authorize('edit');
$delete_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('delete') : contentbuilder::authorize('delete');
$view_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('view') : contentbuilder::authorize('view');
$fullarticle_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('fullarticle') : contentbuilder::authorize('fullarticle');
?>
<?php Factory::getApplication()->getDocument()->addStyleDeclaration($this->theme_css);?>
<?php Factory::getApplication()->getDocument()->addScriptDeclaration($this->theme_js);?>
<a name="article_up"></a>
<script type="text/javascript">
<!--
function contentbuilder_delete(){
    var confirmed = confirm('<?php echo Text::_('COM_CONTENTBUILDER_CONFIRM_DELETE_MESSAGE');?>');
    if(confirmed){
        location.href = '<?php echo 'index.php?option=com_contentbuilder&controller=edit&task=delete'.(CBRequest::getVar('tmpl', '') != '' ? '&tmpl='.CBRequest::getVar('tmpl', '') : '').(CBRequest::getVar('layout', '') != '' ? '&layout='.CBRequest::getVar('layout', '') : '').'&view=edit&id='.CBRequest::getInt('id', 0).'&cid[]='.CBRequest::getCmd('record_id', 0).'&Itemid='.CBRequest::getInt('Itemid',0).'&limitstart='.CBRequest::getInt('limitstart',0).'&filter_order='.CBRequest::getCmd('filter_order'); ?>';
    }
}
//-->
</script>
<div class="cbEditableWrapper" id="cbEditableWrapper<?php echo $this->id; ?>">
<?php
if($this->show_page_heading&& $this->page_title){
?>
<h1 class="contentheading">
<?php echo $this->page_title; ?>
</h1>
<?php
}
?>
<?php echo  $this->event->afterDisplayTitle;?>
<?php
ob_start();
?>
<div class="cbToolBar mb-5" style="float: right; text-align: right;">
<?php
if( $this->record_id && $edit_allowed && $this->create_articles && $fullarticle_allowed){
?>
<button class="btn btn-sm btn-primary cbButton cbArticleSettingsButton" onclick="if(document.getElementById('cbArticleOptions').style.display == 'none'){document.getElementById('cbArticleOptions').style.display='block'}else{document.getElementById('cbArticleOptions').style.display='none'};"><?php echo Text::_('COM_CONTENTBUILDER_SHOW_ARTICLE_SETTINGS')?></button>
<?php
}
if ( ($edit_allowed || $new_allowed) && !$this->edit_by_type) {
    if(CBRequest::getVar('cb_controller') != 'edit' && !CBRequest::getVar('return','') && !$this->latest){
?>
<button class="btn btn-sm btn-primary cbButton cbApplyButton" onclick="document.getElementById('contentbuilder_task').value='apply';contentbuilder.onSubmit();"><?php echo trim($this->apply_button_title) != '' ? htmlentities($this->apply_button_title, ENT_QUOTES, 'UTF-8') : Text::_('COM_CONTENTBUILDER_APPLY')?></button>
<?php
    }
?>
<button class="btn btn-sm btn-primary cbButton cbSaveButton" onclick="<?php echo $this->latest ? "document.getElementById('contentbuilder_task').value='apply';" : ''?>contentbuilder.onSubmit();"><?php echo trim($this->save_button_title) != '' ? htmlentities($this->save_button_title, ENT_QUOTES, 'UTF-8') : Text::_('COM_CONTENTBUILDER_SAVE')?></button>
<?php
}else if( $this->record_id && $edit_allowed && $this->create_articles && $this->edit_by_type && $fullarticle_allowed){
?>
<button class="btn btn-sm btn-primary cbButton cbArticleSettingsButton" onclick="document.getElementById('contentbuilder_task').value='apply';contentbuilder.onSubmit();"><?php echo Text::_('COM_CONTENTBUILDER_APPLY_ARTICLE_SETTINGS')?></button>
<?php
}
if ($this->record_id && $delete_allowed) {
?> 
<button class="btn btn-sm btn-primary cbButton cbDeleteButton" onclick="contentbuilder_delete();"><?php echo Text::_('COM_CONTENTBUILDER_DELETE')?></button>
<?php
}
if(!CBRequest::getInt('backtolist',0) && !CBRequest::getVar('return','')){
    if(!CBRequest::getInt('jsback',0)){
        if($this->back_button){
?>
<a class="btn btn-sm btn-primary cbButton cbBackButton" href="<?php echo Route::_( 'index.php?option=com_contentbuilder&controller=details'.(CBRequest::getVar('layout', '') != '' ? '&layout='.CBRequest::getVar('layout', '') : '').'&id='.CBRequest::getInt('id', 0).'&record_id='.CBRequest::getCmd('record_id', 0).(CBRequest::getVar('tmpl', '') != '' ? '&tmpl='.CBRequest::getVar('tmpl', '') : '').'&Itemid='.CBRequest::getInt('Itemid',0).'&limitstart='.CBRequest::getInt('limitstart',0).'&filter_order='.CBRequest::getCmd('filter_order') ); ?>"><?php echo Text::_('COM_CONTENTBUILDER_BACK')?></a>
<?php
        }
    }else{
?>
<button class="button btn-sm btn btn-primary cbButton cbBackButton" onclick="history.back(-1);void(0);"><?php echo Text::_('COM_CONTENTBUILDER_BACK')?></button>
<?php       
    }
}else{
    if($this->back_button && !CBRequest::getVar('return','')){
?>
<a class="btn btn-sm btn-primary cbButton cbBackButton" href="<?php echo Route::_( 'index.php?option=com_contentbuilder&controller=list'.(CBRequest::getVar('layout', '') != '' ? '&layout='.CBRequest::getVar('layout', '') : '').'&id='.CBRequest::getInt('id', 0).'&limitstart='.CBRequest::getInt('limitstart',0).'&filter_order='.CBRequest::getCmd('filter_order').(CBRequest::getVar('tmpl', '') != '' ? '&tmpl='.CBRequest::getVar('tmpl', '') : '').'&Itemid='.CBRequest::getInt('Itemid',0) ); ?>"><?php echo Text::_('COM_CONTENTBUILDER_BACK')?></a>
<?php
    }
}
?>
</div>
<?php
$buttons = ob_get_contents();
ob_end_clean();

if( CBRequest::getInt('cb_show_top_bar',1) ){
    ?>
    <div style="clear:right;"></div>
    <?php
    echo $buttons;
}

if(CBRequest::getInt('cb_show_author',1)){
?>

<?php if($this->created): ?>
<span class="small created-by"><?php echo Text::_('COM_CONTENTBUILDER_CREATED_ON');?> <?php echo HTMLHelper::_('date', $this->created, Text::_('DATE_FORMAT_LC2')); ?></span>
<?php endif; ?>

<?php if($this->created_by): ?>
<span class="small created-by"><?php echo Text::_('COM_CONTENTBUILDER_BY');?> <?php echo $this->created_by; ?></span><br/>
<?php endif; ?>
<?php
}

if(CBRequest::getInt('cb_show_author',1)){
?>

<?php if($this->modified_by): ?>

<?php if($this->modified): ?>
<span class="small created-by"><?php echo Text::_('COM_CONTENTBUILDER_LAST_UPDATED_ON');?> <?php echo HTMLHelper::_('date', $this->modified, Text::_('DATE_FORMAT_LC2')); ?></span>
<?php endif; ?>

<span class="small created-by"><?php echo Text::_('COM_CONTENTBUILDER_BY');?> <?php echo $this->modified_by; ?></span>

<?php endif;
}

if($this->create_articles && $fullarticle_allowed){

?>
<?php
if(!$this->edit_by_type){
?>
<form class="form-horizontal mt-5 mb-5" name="adminForm" id="adminForm" onsubmit="return false;" action="<?php echo Route::_( 'index.php?option=com_contentbuilder&controller=edit'.(CBRequest::getVar('layout', '') != '' ? '&layout='.CBRequest::getVar('layout', '') : '').'&id='.CBRequest::getInt('id', 0).'&record_id='.CBRequest::getCmd('record_id',  '').(CBRequest::getVar('tmpl', '') != '' ? '&tmpl='.CBRequest::getVar('tmpl', '') : '').'&Itemid='.CBRequest::getInt('Itemid',0).'&limitstart='.CBRequest::getInt('limitstart',0).'&filter_order='.CBRequest::getCmd('filter_order') ); ?>" method="post" enctype="multipart/form-data">
<?php
}
?>
<?php
if($this->edit_by_type){
?>
<form class="mt-5 mb-5" name="adminForm" id="adminForm" onsubmit="return false;" action="<?php echo Route::_( 'index.php?option=com_contentbuilder&controller=edit'.(CBRequest::getVar('layout', '') != '' ? '&layout='.CBRequest::getVar('layout', '') : '').'&id='.CBRequest::getInt('id', 0).'&record_id='.CBRequest::getCmd('record_id',  '').(CBRequest::getVar('tmpl', '') != '' ? '&tmpl='.CBRequest::getVar('tmpl', '') : '').'&Itemid='.CBRequest::getInt('Itemid',0).'&limitstart='.CBRequest::getInt('limitstart',0).'&filter_order='.CBRequest::getCmd('filter_order') ); ?>" method="post" enctype="multipart/form-data">
<?php
}
?>

<div id="cbArticleOptions" style="display:none;">

<fieldset class="adminform">
    <ul class="adminformlist">
        <li><?php echo $this->article_options->getLabel('alias'); ?>
            <?php echo $this->article_options->getInput('alias'); ?></li>

        <li><?php echo $this->article_options->getLabel('catid'); ?>
            <?php echo $this->article_options->getInput('catid'); ?></li>

        <!--<li><?php echo $this->article_options->getLabel('state'); ?>
	<?php echo $this->article_options->getInput('state'); ?></li>-->
        
        <li><?php echo $this->article_options->getLabel('access'); ?>
            <?php echo $this->article_options->getInput('access'); ?></li>

        <li><?php echo $this->article_options->getLabel('featured'); ?>
            <?php echo $this->article_options->getInput('featured'); ?></li>

        <li><?php echo $this->article_options->getLabel('language'); ?>
            <?php echo $this->article_options->getInput('language'); ?></li>
        <?php
        if(!$this->limited_options){
        ?>
        <li><?php echo $this->article_options->getLabel('id'); ?>
            <?php echo $this->article_options->getInput('id'); ?></li>
        <?php
        }
        ?>
    </ul>
    <div class="clr"></div>
</fieldset>

<fieldset class="panelform">
    <ul class="adminformlist">
        
        <?php
        if(!$this->limited_options && Factory::getApplication()->isClient('administrator')){
        ?>
        <li><?php echo $this->article_options->getLabel('created_by'); ?>
            <?php echo $this->article_options->getInput('created_by'); ?></li>
          
        <?php
        }
        ?>
        <li><?php echo $this->article_options->getLabel('created_by_alias'); ?>
            <?php echo $this->article_options->getInput('created_by_alias'); ?></li>
        
        <?php
        if(!$this->limited_options){
        ?>
        <li><?php echo $this->article_options->getLabel('created'); ?>
            <?php echo $this->article_options->getInput('created'); ?></li>
        <?php
        }
        ?>
        
        <li><?php echo $this->article_options->getLabel('publish_up'); ?>
            <?php echo $this->article_options->getInput('publish_up'); ?></li>
                                            
        <li><?php echo $this->article_options->getLabel('publish_down'); ?>
            <?php echo $this->article_options->getInput('publish_down'); ?></li>
        <?php
        if(!$this->limited_options){
        ?>                                
        <?php if ($this->article_settings->modified_by) : ?>
            <li><?php echo $this->article_options->getLabel('modified_by'); ?>
                <?php echo $this->article_options->getInput('modified_by'); ?></li>
                                                        
            <li><?php echo $this->article_options->getLabel('modified'); ?>
                <?php echo $this->article_options->getInput('modified'); ?></li>
        <?php endif; ?>
                                            
        <?php if ($this->article_settings->version) : ?>
            <li><?php echo $this->article_options->getLabel('version'); ?>
                <?php echo $this->article_options->getInput('version'); ?></li>
        <?php endif; ?>
                                            
        <?php if ($this->article_settings->hits) : ?>
            <li><?php echo $this->article_options->getLabel('hits'); ?>
                <?php echo $this->article_options->getInput('hits'); ?></li>
        <?php endif; ?>
        <?php
        }
        ?>
    </ul>
</fieldset>

<?php
if(!$this->limited_options){
?> 
<?php $fieldSets = $this->article_options->getFieldsets('attribs');?>
<?php foreach ($fieldSets as $name => $fieldSet) : ?>
    <?php if(!in_array($name, array('editorConfig', 'basic-limited'))) : ?>

    <?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
        <p class="tip"><?php echo $this->escape(Text::_($fieldSet->description)); ?></p>
    <?php endif; ?>
    <fieldset class="panelform">
        <ul class="adminformlist">
            <?php foreach ($this->article_options->getFieldset($name) as $field) : ?>
                <li><?php echo $field->label; ?><?php echo $field->input; ?></li>
            <?php endforeach; ?>
        </ul>
    </fieldset>
    <?php endif; ?>
<?php endforeach; ?>
<?php
}
?>
<fieldset class="panelform">
    <?php echo $this->article_options->getLabel('metadesc'); ?>
    <?php echo $this->article_options->getInput('metadesc'); ?>

    <?php echo $this->article_options->getLabel('metakey'); ?>
    <?php echo $this->article_options->getInput('metakey'); ?>
    <?php
    if(!$this->limited_options){
    ?>
    <?php foreach ($this->article_options->getGroup('metadata') as $field): ?>
        <?php if ($field->hidden): ?>
            <?php echo $field->input; ?>
        <?php else: ?>
            <?php echo $field->label; ?>
            <?php echo $field->input; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php
    }
    ?>
</fieldset>

</div>
<?php

        if( CBRequest::getVar('tmpl', '') != '' ){
        ?>
        <input type="hidden" name="tmpl" value="<?php echo CBRequest::getVar('tmpl', ''); ?>"/>   
        <?php
        }
        ?>
        <input type="hidden" name="Itemid" value="<?php echo CBRequest::getInt('Itemid',0); ?>"/>
        <input type="hidden" name="task" id="contentbuilder_task" value="save"/>
        <input type="hidden" name="backtolist" value="<?php echo CBRequest::getInt('backtolist',0);?>"/>
        <input type="hidden" name="return" value="<?php echo CBRequest::getVar('return','');?>"/>
        <?php echo HTMLHelper::_('form.token'); ?>
        <?php
        if($this->edit_by_type){
        ?>
        </form>
        <?php
        }
        ?>
        <?php echo $this->event->beforeDisplayContent; ?>
        <?php echo $this->toc ?>
        <?php echo $this->tpl ?>
        <?php echo $this->event->afterDisplayContent; ?>
        <br/>
        <?php
        if( CBRequest::getInt('cb_show_bottom_bar', 1) ){
            
            echo $buttons;
            ?>
            <div style="clear:right;"></div>
            <?php
        }
        ?>
<?php
if(!$this->edit_by_type){
?>
</form>
<?php
}
?>
<?php
}else{
    if($this->edit_by_type){
?>
    <form class="mt-5" name="adminForm" id="adminForm" onsubmit="return false;" action="<?php echo Route::_( 'index.php?option=com_contentbuilder&controller=edit'.(CBRequest::getVar('layout', '') != '' ? '&layout='.CBRequest::getVar('layout', '') : '').'&id='.CBRequest::getInt('id', 0).'&record_id='.CBRequest::getCmd('record_id',  '').(CBRequest::getVar('tmpl', '') != '' ? '&tmpl='.CBRequest::getVar('tmpl', '') : '').'&Itemid='.CBRequest::getInt('Itemid',0).'&limitstart='.CBRequest::getInt('limitstart',0).'&filter_order='.CBRequest::getCmd('filter_order') ); ?>" method="post" enctype="multipart/form-data">
    <?php
    if( CBRequest::getVar('tmpl', '') != '' ){
    ?>
    <input type="hidden" name="tmpl" value="<?php echo CBRequest::getVar('tmpl', ''); ?>"/>   
    <?php
    }
    ?>
    <input type="hidden" name="Itemid" value="<?php echo CBRequest::getInt('Itemid',0); ?>"/>
    <input type="hidden" name="task" id="contentbuilder_task" value="save"/>
    <input type="hidden" name="backtolist" value="<?php echo CBRequest::getInt('backtolist',0);?>"/>
    <input type="hidden" name="return" value="<?php echo CBRequest::getVar('return','');?>"/>
    <?php echo HTMLHelper::_('form.token'); ?>
    </form>
    <?php echo $this->event->beforeDisplayContent; ?>
    <?php echo $this->toc ?>
    <?php echo $this->tpl ?>
    <?php echo $this->event->afterDisplayContent; ?>
    <br/>
    <?php
    if( CBRequest::getInt('cb_show_bottom_bar', 1) ){
        
        echo $buttons;
        ?>
        <div style="clear:right;"></div>
        <?php
    }
    ?>
<?php
    } else {
?>
    <form class="form-horizontal name="adminForm" id="adminForm" onsubmit="return false;" action="<?php echo Route::_( 'index.php?option=com_contentbuilder&controller=edit'.(CBRequest::getVar('layout', '') != '' ? '&layout='.CBRequest::getVar('layout', '') : '').'&id='.CBRequest::getInt('id', 0).'&record_id='.CBRequest::getCmd('record_id',  '').(CBRequest::getVar('tmpl', '') != '' ? '&tmpl='.CBRequest::getVar('tmpl', '') : '').'&Itemid='.CBRequest::getInt('Itemid',0).'&limitstart='.CBRequest::getInt('limitstart',0).'&filter_order='.CBRequest::getCmd('filter_order') ); ?>" method="post" enctype="multipart/form-data">
    <?php echo $this->event->beforeDisplayContent; ?>
    <?php echo $this->toc ?>
    <?php echo $this->tpl ?>
    <?php echo $this->event->afterDisplayContent; ?>
    <?php
    if( CBRequest::getVar('tmpl', '') != '' ){
    ?>
    <input type="hidden" name="tmpl" value="<?php echo CBRequest::getVar('tmpl', ''); ?>"/>   
    <?php
    }
    ?>
    <input type="hidden" name="Itemid" value="<?php echo CBRequest::getInt('Itemid',0); ?>"/>
    <input type="hidden" name="task" id="contentbuilder_task" value="save"/>
    <input type="hidden" name="backtolist" value="<?php echo CBRequest::getInt('backtolist',0);?>"/>
    <input type="hidden" name="return" value="<?php echo CBRequest::getVar('return','');?>"/>
    <?php echo HTMLHelper::_('form.token'); ?>
    </form>
    <?php
    if( CBRequest::getInt('cb_show_bottom_bar', 1) ){

        echo $buttons;
        ?>
        <div style="clear:both;"></div>
        <?php
    }
    ?>
<?php
    }
}
?>
</div>

