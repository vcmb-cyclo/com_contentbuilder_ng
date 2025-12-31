<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\Form;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView;
use CB\Component\Contentbuilder\Administrator\ContentbuilderHelper;

require_once(JPATH_SITE .'/administrator/components/com_contentbuilder/classes/pane/CBTabs.php');



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

        $form->config = $form->config ? unserialize(base64_decode($form->config)) : null;

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
