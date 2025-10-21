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

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

class ContentbuilderModelForms extends CBModel
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
    }

    function copy()
    {
        $table = $this->getTable('form');
        $cids = CBRequest::getVar('cid', array(), '', 'array');
        ArrayHelper::toInteger($cids);

        if (!count($cids))
            return;

        $this->_db->setQuery(' Select * From #__contentbuilder_forms ' .
            '  Where id In ( ' . implode(',', $cids) . ')');
        $result = $this->_db->loadObjectList();

        foreach ($result as $obj) {
            $origId = $obj->id;
            unset($obj->id);

            $obj->name = 'Copy of ' . $obj->name;
            $obj->published = 0;
            $this->_db->insertObject('#__contentbuilder_forms', $obj);
            $insertId = $this->_db->insertid();

            // elements
            $this->_db->setQuery(' Select * From #__contentbuilder_elements ' .
                '  Where form_id = ' . $origId);
            $elements = $this->_db->loadObjectList();
            foreach ($elements as $element) {
                unset($element->id);
                $element->form_id = $insertId;
                $this->_db->insertObject('#__contentbuilder_elements', $element);
            }

            // list states
            $this->_db->setQuery(' Select * From #__contentbuilder_list_states ' .
                '  Where form_id = ' . $origId);
            $elements = $this->_db->loadObjectList();
            foreach ($elements as $element) {
                unset($element->id);
                $element->form_id = $insertId;
                $this->_db->insertObject('#__contentbuilder_list_states', $element);
            }
            // XDA-Gil fix 'Copy of Form' in Component Menu in Backen CB View
            // contentbuilder::createBackendMenuItem($insertId, $obj->name, true);
        }

        $table->reorder();
    }

    function setPublished()
    {
        $cids = CBRequest::getVar('cid', array(), '', 'array');
        ArrayHelper::toInteger($cids);
        $this->_db->setQuery(' Update #__contentbuilder_forms ' .
            '  Set published = 1 Where id In ( ' . implode(',', $cids) . ')');
        $this->_db->execute();

    }

    function setUnpublished()
    {
        $cids = CBRequest::getVar('cid', array(), '', 'array');
        ArrayHelper::toInteger($cids);
        $this->_db->setQuery(' Update #__contentbuilder_forms ' .
            '  Set published = 0 Where id In ( ' . implode(',', $cids) . ')');
        $this->_db->execute();
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

        $orderby = '';
        $filter_order = $this->getState('forms_filter_order');
        $filter_order_Dir = $this->getState('forms_filter_order_Dir');

        /* Error handling is never a bad thing*/
        if (!empty($filter_order) && !empty($filter_order_Dir)) {
            $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
        }

        return $orderby;
    }


    /**
     * @return string The query
     */
    private function _buildQuery()
    {

        $where = '';

        // PUBLISHED FILTER SELECTED?
        $filter_state = '';
        if ($this->getState('forms_filter_state') == 'P' || $this->getState('forms_filter_state') == 'U') {
            $published = 0;
            if ($this->getState('forms_filter_state') == 'P') {
                $published = 1;
            }

            $filter_state .= ' published = ' . $published;
        }


        if ($this->getState('forms_filter_tag') != '') {
            if ($filter_state != '') {
                $filter_state .= ' And ';
            }
            $filter_state .= ' Lower(`tag`) Like ' . $this->_db->Quote(strtolower($this->getState('forms_filter_tag')));
        }


        if ($filter_state != '') {
            $where = ' Where ';
        }

        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_forms ' . $where . $filter_state . $this->buildOrderBy();
    }

    function saveOrder()
    {
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);

        $total = count($items);
        $row = $this->getTable('form');
        $groupings = array();

        $order = CBRequest::getVar('order', array(), 'post', 'array');
        ArrayHelper::toInteger($order);

        // update ordering values
        for ($i = 0; $i < $total; $i++) {
            $row->load($items[$i]);
            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];
                if (!$row->store()) {
                    $this->setError($row->getError());
                    return false;
                }
            } // if
        } // for


        $row->reorder();
    }


    function getTags()
    {
        $this->_db->setQuery("Select Distinct `tag` As `tag` From #__contentbuilder_forms Order by `tag` Desc");
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

        return $this->_data;
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


