<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView;

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');

class ContentbuilderViewUsers extends HtmlView
{
    function display($tpl = null)
    {

        ToolBarHelper::title(   '<span style="display:inline-block; vertical-align:middle">' . Text::_( 'COM_CONTENTBUILDER_FORMS' ) . '</span>', 'logo_left.png' );
        ToolBarHelper::editList();

        // Get data from the model
        $items = $this->get( 'Data');
        $pagination = $this->get('Pagination');

        $state = $this->get( 'state' );
        $lists['users_search'] = $state->get( 'users_search' );
        $lists['order_Dir'] = $state->get( 'users_filter_order_Dir' );
        $lists['order'] = $state->get( 'users_filter_order' );
        $lists['state']	= HTMLHelper::_('grid.state', $state->get( 'users_filter_state' ) );
        $lists['limitstart'] = $state->get( 'limitstart' );
        
        $ordering = ($lists['order'] == 'ordering');

        $this->ordering = $ordering;
        $this->lists = $lists;

        $this->items = $items;
        $this->pagination = $pagination;
        parent::display($tpl);
    }
}
