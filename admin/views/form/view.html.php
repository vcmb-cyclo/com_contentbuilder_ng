<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'pane' . DS . 'CBTabs.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');

class ContentbuilderViewForm extends HtmlView
{
    function display($tpl = null)
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $document = Factory::getApplication()->getDocument();
        $document->addScript(Uri::root(true) . '/administrator/components/com_contentbuilder/assets/js/jscolor/jscolor.js');

        echo '
        <style type="text/css">
        .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
        </style>
        ';

        echo '<link rel="stylesheet" href="' . Uri::root(true) . '/administrator/components/com_contentbuilder/views/bluestork.fix.css" type="text/css" />';

        $form = $this->get('Form');
        $elements = $this->get('Data');
        $all_elements = $this->get('AllElements');
        $pagination = $this->get('Pagination');
        $isNew = ($form->id < 1);

        $text = $isNew ? Text::_('COM_CONTENTBUILDER_NEW') : Text::_('COM_CONTENTBUILDER_EDIT');

        ToolBarHelper::title('ContentBuilder :: ' . ($isNew ? Text::_('COM_CONTENTBUILDER_FORM') : $form->name) . ' : <small><small>[ ' . $text . ' ]</small></small></span>', 'logo_left.png');

        //ToolBarHelper::customX('linkable', 'default', '', Text::_('COM_CONTENTBUILDER_LINKABLE'), false);
        //ToolBarHelper::customX('not_linkable', 'default', '', Text::_('COM_CONTENTBUILDER_NOT_LINKABLE'), false);

        ToolBarHelper::apply();
        ToolBarHelper::save();

        ToolBarHelper::custom('saveNew', 'save', '', Text::_('COM_CONTENTBUILDER_SAVENEW'), false);
        ToolBarHelper::custom('list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_LIST_INCLUDE'), false);
        ToolBarHelper::custom('no_list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_NO_LIST_INCLUDE'), false);
        ToolBarHelper::custom('editable', 'edit', '', Text::_('COM_CONTENTBUILDER_EDITABLE'), false);
        ToolBarHelper::custom('not_editable', 'edit', '', Text::_('COM_CONTENTBUILDER_NOT_EDITABLE'), false);
        ToolBarHelper::custom('listpublish', 'publish', '', Text::_('COM_CONTENTBUILDER_PUBLISH'), false);
        ToolBarHelper::custom('listunpublish', 'unpublish', '', Text::_('COM_CONTENTBUILDER_UNPUBLISH'), false);

        //ToolBarHelper::deleteList();
        if ($isNew) {
            ToolBarHelper::cancel();
        } else {
            // for existing items the button is renamed `close`
            ToolBarHelper::cancel('cancel', 'Close');
        }

        $state = $this->get('state');
        $lists['order_Dir'] = $state->get('elements_filter_order_Dir');
        $lists['order'] = $state->get('elements_filter_order');
        $lists['limitstart'] = $state->get('limitstart');

        $ordering = ($lists['order'] == 'ordering');

        $gmap = array();
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = 'SELECT CONCAT( REPEAT(\'..\', COUNT(parent.id) - 1), node.title) as text, node.id as value'
            . ' FROM #__usergroups AS node, #__usergroups AS parent'
            . ' WHERE node.lft BETWEEN parent.lft AND parent.rgt'
            . ' GROUP BY node.id'
            . ' ORDER BY node.lft';
        $db->setQuery($query);
        $gmap = $db->loadObjectList();

        $form->config = $form->config ? unserialize(cb_b64dec($form->config)) : null;

        $actionPlugins = $this->get('ListStatesActionPlugins');
        $verificationPlugins = $this->get('VerificationPlugins');
        $themePlugins = $this->get('ThemePlugins');

        $this->list_states_action_plugins = $actionPlugins;
        $this->verification_plugins = $verificationPlugins;
        $this->theme_plugins = $themePlugins;
        $this->gmap = $gmap;
        $this->ordering = $ordering;
        $this->form = $form;
        $this->elements = $elements;
        $this->all_elements = $all_elements;
        $this->pagination = $pagination;

        parent::display($tpl);
    }
}
