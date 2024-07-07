<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView;

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');

class ContentbuilderViewStorage extends HtmlView
{
    function display($tpl = null)
    {
	    Factory::getApplication()->input->set('hidemainmenu', true);

        $document = Factory::getApplication()->getDocument();
        $document->addScript( Uri::root(true) . '/administrator/components/com_contentbuilder/assets/js/jscolor/jscolor.js' );

        echo '
        <style type="text/css">
        .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
        </style>
        ';
        echo '<link rel="stylesheet" href="'.Uri::root(true).'/administrator/components/com_contentbuilder/views/bluestork.fix.css" type="text/css" />';
        $tables     = $this->get('DbTables');
        $form     = $this->get('Storage');
        $elements  = $this->get('Data');
        $pagination   = $this->get('Pagination');
        $isNew        = ($form->id < 1);

        $text = $isNew ? Text::_( 'COM_CONTENTBUILDER_NEW' ) : Text::_( 'COM_CONTENTBUILDER_EDIT' );

	    ToolBarHelper::title(   'ContentBuilder :: ' . ($isNew ? Text::_( 'COM_CONTENTBUILDER_STORAGES' ) : $form->title) .' : <small><small>[ ' . $text.' ]</small></small></span>', 'logo_left.png' );
        
        ToolBarHelper::apply();
        ToolBarHelper::save();
        
        if(version_compare(CBJOOMLAVERSION, '3.0', '<')){
            ToolBarHelper::customX('saveNew', 'save', '', Text::_('COM_CONTENTBUILDER_SAVENEW'), false);
            ToolBarHelper::customX('listpublish', 'publish', '', Text::_('COM_CONTENTBUILDER_PUBLISH'), false);
            ToolBarHelper::customX('listunpublish', 'unpublish', '', Text::_('COM_CONTENTBUILDER_UNPUBLISH'), false);
            ToolBarHelper::customX('listdelete', 'delete', '', Text::_('COM_CONTENTBUILDER_DELETE_FIELDS'), false);
        } else {
            ToolBarHelper::custom('saveNew', 'save', '', Text::_('COM_CONTENTBUILDER_SAVENEW'), false);
            ToolBarHelper::custom('listpublish', 'publish', '', Text::_('COM_CONTENTBUILDER_PUBLISH'), false);
            ToolBarHelper::custom('listunpublish', 'unpublish', '', Text::_('COM_CONTENTBUILDER_UNPUBLISH'), false);
            ToolBarHelper::custom('listdelete', 'delete', '', Text::_('COM_CONTENTBUILDER_DELETE_FIELDS'), false);
        }
        
        //ToolBarHelper::deleteList();
        if ($isNew) {
            ToolBarHelper::cancel();
        } else {
            // for existing items the button is renamed `close`
            ToolBarHelper::cancel( 'cancel', 'Close' );
        }

        $state = $this->get( 'state' );
        $lists['order_Dir'] = $state->get( 'fields_filter_order_Dir' );
        $lists['order'] = $state->get( 'fields_filter_order' );
        $lists['limitstart'] = $state->get( 'limitstart' );

        $ordering = ($lists['order'] == 'ordering');

        $this->ordering = $ordering;
        $this->form = $form;
        $this->elements = $elements;
        $this->tables = $tables;
        $this->pagination = $pagination;
        parent::display($tpl);
    }
}
