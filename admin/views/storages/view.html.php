<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL 
 * @license     GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');

class ContentbuilderViewStorages extends HtmlView
{
    function display($tpl = null)
    {
        echo '
        <style type="text/css">
        .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
        </style>
        ';
        echo '<link rel="stylesheet" href="' . Uri::root(true) . '/administrator/components/com_contentbuilder/views/bluestork.fix.css" type="text/css" />';

        ToolBarHelper::addNew();
        ToolBarHelper::editList();

        ToolBarHelper::title('ContentBuilder :: ' . Text::_('COM_CONTENTBUILDER_STORAGES') . '</span>', 'logo_left.png');

        ToolBarHelper::deleteList();

        ToolBarHelper::preferences('com_contentbuilder');

        // Get data from the model
        $items = $this->get('Data');
        $pagination = $this->get('Pagination');

        $state = $this->get('state');
        $lists['order_Dir'] = $state->get('storages_filter_order_Dir');
        $lists['order'] = $state->get('storages_filter_order');
        $lists['state'] = HTMLHelper::_('grid.state', $state->get('storages_filter_state'));
        $lists['limitstart'] = $state->get('limitstart');

        $ordering = ($lists['order'] == 'ordering');

        $this->ordering = $ordering;
        $this->lists = $lists;
        $this->items = $items;
        $this->pagination = $pagination;
        parent::display($tpl);
    }
}
