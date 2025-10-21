<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

class ContentbuilderModelPublicforms extends CBModel
{
    /**
     * Items total
     * @var integer
     */
    private $_total = null;

    /**
     * Pagination object
     * @var object
     */
    private $_pagination = null;

    private $frontend = false;

    private $_menu_item = false;

    private $forms = array();

    private $show_permissions = false;

    private $show_permissions_new = false;

    private $show_permissions_edit = false;

    private $show_introtext = false;

    private $show_tags = true;

    private $show_id = false;

    private $items = array();

    private $_show_page_heading = false;

    function __construct($config)
    {
        parent::__construct($config);

        $mainframe = Factory::getApplication();
        $option = 'com_contentbuilder';

        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->get('list_limit'), 'int');
        $limitstart = CBRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $filter_order = $mainframe->getUserStateFromRequest($option . 'forms_filter_order', 'filter_order', '`name`', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'forms_filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

        $this->setState('forms_filter_order', $filter_order);
        $this->setState('forms_filter_order_Dir', $filter_order_Dir);

        $filter_state = $mainframe->getUserStateFromRequest($option . 'forms_filter_state', 'filter_state', '', 'word');
        $this->setState('forms_filter_state', $filter_state);

        $filter_tag = $mainframe->getUserStateFromRequest($option . 'forms_filter_tag', 'filter_tag', '', 'string');
        $this->setState('forms_filter_tag', $filter_tag);

        $this->frontend = Factory::getApplication()->isClient('site');

        if ($this->frontend && CBRequest::getInt('Itemid', 0)) {
            $this->_menu_item = true;

            // try menu item
            $forms = null;

            $menu = Factory::getApplication()->getMenu();
            $item = $menu->getActive();
            if (is_object($item)) {

                if ($item->getParams()->get('forms', null) !== null) {
                    $forms = $item->getParams()->get('forms', null);
                }

                if ($item->getParams()->get('cb_show_permission_column', null) !== null) {
                    $this->show_permissions = $item->getParams()->get('cb_show_permission_column', null);
                }

                if ($item->getParams()->get('cb_show_permission_new_column', null) !== null) {
                    $this->show_permissions_new = $item->getParams()->get('cb_show_permission_new_column', null);
                }

                if ($item->getParams()->get('cb_show_permission_edit_column', null) !== null) {
                    $this->show_permissions_edit = $item->getParams()->get('cb_show_permission_edit_column', null);
                }

                if ($item->getParams()->get('show_page_heading', null) !== null) {
                    $this->_show_page_heading = $item->getParams()->get('show_page_heading', null);
                }

                if ($item->getParams()->get('cb_show_introtext', null) !== null) {
                    $this->show_introtext = $item->getParams()->get('cb_show_introtext', null);
                }

                if ($item->getParams()->get('cb_show_tags', null) !== null) {
                    $this->show_tags = $item->getParams()->get('cb_show_tags', null);
                }

                if ($item->getParams()->get('cb_show_id', null) !== null) {
                    $this->show_id = $item->getParams()->get('cb_show_id', null);
                }
            }

            if ($forms !== null) {
                if (!is_array($forms)) {
                    $forms2 = explode(',', $forms);
                } else {
                    $forms2 = $forms;
                }
                foreach ($forms2 as $form) {
                    $this->forms[] = intval($form);
                }
            }
        }
    }

    /*
     *
     * MAIN LIST AREA
     * 
     */

    private function buildOrderBy()
    {
        $mainframe = Factory::getApplication();
        $option = 'com_contentbuilder';

        $orderby = ' Order By ordering';
        $filter_order = $this->getState('forms_filter_order');
        $filter_order_Dir = $this->getState('forms_filter_order_Dir');

        /* Error handling is never a bad thing*/
        //if(!empty($filter_order) && !empty($filter_order_Dir) ) {
        //$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
        //}

        return $orderby;
    }


    /**
     * @return string The query
     */
    private function _buildQuery()
    {

        $filter_state = '';

        if ($this->getState('forms_filter_tag') != '') {
            $filter_state .= ' And Lower(`tag`) Like ' . $this->_db->Quote(strtolower($this->getState('forms_filter_tag'))) . ' ';
        }

        $in = '';
        if (count($this->forms)) {
            $in = ' id In (' . implode(',', $this->forms) . ') And ';
        }

        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_forms Where ' . $in . ' published = 1 ' . $filter_state . $this->buildOrderBy();
    }

    function getShowPageHeading()
    {
        return $this->_show_page_heading;
    }

    function getShowPermissions()
    {
        return $this->show_permissions;
    }

    function getShowPermissionsNew()
    {
        return $this->show_permissions_new;
    }

    function getShowPermissionsEdit()
    {
        return $this->show_permissions_edit;
    }

    function getShowIntrotext()
    {
        return $this->show_introtext;
    }

    function getShowTags()
    {
        return $this->show_tags;
    }

    function getShowId()
    {
        return $this->show_id;
    }

    function getPermissions()
    {
        $perms = array();
        if ($this->show_permissions) {

            foreach ($this->items as $item) {

                contentbuilder::setPermissions($item->id, '', '_fe');
                $view = contentbuilder::authorizeFe('view');
                $new = contentbuilder::authorizeFe('new');
                $edit = contentbuilder::authorizeFe('edit');

                $perms[$item->id] = array('view' => $view, 'new' => $new, 'edit' => $edit);
            }
        }
        return $perms;
    }

    function getTags()
    {
        $this->_db->setQuery("Select Distinct `tag` As `tag` From #__contentbuilder_forms Where published = 1 Order by `tag` Asc");
        return $this->_db->loadObjectList();
    }

    /**
     * Gets the currencies
     * @return array List of products
     */
    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        $this->items = $this->_data;
        return $this->items;
    }

    function getTotal()
    {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }

    function getPagination()
    {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }

}
