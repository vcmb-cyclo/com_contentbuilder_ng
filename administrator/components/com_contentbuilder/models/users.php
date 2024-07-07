<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Pagination\Pagination;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

class ContentbuilderModelUsers extends CBModel
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

        $this->_db = Factory::getContainer()->get(DatabaseInterface::class);

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

        $filter_order = $mainframe->getUserStateFromRequest($option . 'users_filter_order', 'filter_order', '`users`.`id`', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'users_filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

        $this->setState('users_filter_order', $filter_order);
        $this->setState('users_filter_order_Dir', $filter_order_Dir);

        $filter_state = $mainframe->getUserStateFromRequest($option . 'users_filter_state', 'filter_state', '', 'word');
        $this->setState('users_filter_state', $filter_state);

        $search = $mainframe->getUserStateFromRequest("$option.users_search", 'users_search', '', 'string');
        $this->setState('users_search', $search);
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
        $filter_order = $this->getState('users_filter_order');
        $filter_order_Dir = $this->getState('users_filter_order_Dir');

        /* Error handling is never a bad thing*/
        if (!empty($filter_order) && !empty($filter_order_Dir) && $filter_order != 'ordering') {
            $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
        }

        return $orderby;
    }


    function setPublished()
    {
        $cids = CBRequest::getVar('cid', array(), '', 'array');
        ArrayHelper::toInteger($cids);
        foreach ($cids as $cid) {
            $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = " . CBRequest::getInt('form_id', 0) . " And userid = " . $cid);
            if (!$this->_db->loadResult() && CBRequest::getInt('form_id', 0) && $cid) {
                $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (" . CBRequest::getInt('form_id', 0) . ", $cid, 1)");
                $this->_db->execute();
            }
        }
        $this->_db->setQuery(' Update #__contentbuilder_users ' .
            '  Set published = 1 Where form_id = ' . CBRequest::getInt('form_id', 0) . ' And userid In ( ' . implode(',', $cids) . ')');
        $this->_db->execute();

    }

    function setUnpublished()
    {
        $cids = CBRequest::getVar('cid', array(), '', 'array');
        ArrayHelper::toInteger($cids);
        foreach ($cids as $cid) {
            $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = " . CBRequest::getInt('form_id', 0) . " And userid = " . $cid);
            if (!$this->_db->loadResult() && CBRequest::getInt('form_id', 0) && $cid) {
                $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (" . CBRequest::getInt('form_id', 0) . ", $cid, 1)");
                $this->_db->execute();
            }
        }
        $this->_db->setQuery(' Update #__contentbuilder_users ' .
            '  Set published = 0 Where form_id = ' . CBRequest::getInt('form_id', 0) . ' And userid In ( ' . implode(',', $cids) . ')');
        $this->_db->execute();
    }

    /**
     * @return string The query
     */
    private function _buildQuery()
    {

        $where = '';

        if (trim($this->getState('users_search')) != '') {
            $where = ' Where users.email Like ' . $this->_db->Quote('%' . $this->getState('users_search') . '%') . ' Or users.id = ' . $this->_db->Quote(intval($this->getState('users_search'))) . ' Or users.username Like ' . $this->_db->Quote('%' . $this->getState('users_search') . '%') . ' Or users.`name` Like ' . $this->_db->Quote('%' . $this->getState('users_search') . '%') . ' ';
        }

        return 'Select SQL_CALC_FOUND_ROWS users.*, contentbuilder_users.verified_view, contentbuilder_users.verified_new, contentbuilder_users.verified_edit, contentbuilder_users.records, contentbuilder_users.published From #__users As users Left Join #__contentbuilder_users As contentbuilder_users On ( users.id = contentbuilder_users.userid And contentbuilder_users.form_id = ' . CBRequest::getInt('form_id', 0) . ' ) ' . $where . $this->buildOrderBy();
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
            echo $this->_db->getErrorMsg();
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
