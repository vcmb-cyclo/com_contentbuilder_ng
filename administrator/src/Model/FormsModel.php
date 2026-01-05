<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Model;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\MVC\Model\ListModel;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use Joomla\Database\QueryInterface;

class FormsModel extends ListModel
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

    public function __construct($config = [])
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

    
    /**
     * Supprime plusieurs formulaires
     * Appelée automatiquement par AdminController
     */
    public function delete($pks = null): bool
    {
        $pks = (array) $pks;
        ArrayHelper::toInteger($pks);

        if (empty($pks)) {
            return false;
        }

        $model = $this->getModel('Form', 'Contentbuilder'); // utilise FormModel pour la suppression complète

        foreach ($pks as $pk) {
            if (!$model->delete([$pk])) {
                $this->setError($model->getError());
                return false;
            }
        }

        return true;
    }

    /*
     *
     * MAIN LIST AREA
     * 
     */

    public function buildOrderBy()
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




    public function saveOrder()
    {
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);

        $total = count($items);
        $row = $this->getTable('Form', '');
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


    public function getTags()
    {
        $db = $this->_db;
        
        $query = $db->getQuery(true)
            ->select('DISTINCT tag AS tag')
            ->from('#__contentbuilder_forms')
            ->where('tag <> ""')
            ->order('tag DESC');
        $db->setQuery($query);
        return $db->loadObjectList();
    }


    protected function getListQuery(): QueryInterface
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__contentbuilder_forms'));

        // Filtre published (tes clés custom)
        $filterState = $this->getState('forms_filter_state');

        if ($filterState === 'P') {
            $query->where($db->quoteName('published') . ' = 1');
        } elseif ($filterState === 'U') {
            $query->where($db->quoteName('published') . ' = 0');
        }

        // Filtre tag
        $tag = (string) $this->getState('forms_filter_tag');
        if ($tag !== '') {
            $query->where('LOWER(' . $db->quoteName('tag') . ') LIKE ' . $db->quote('%' . strtolower($tag) . '%'));
        }

        // Order
        $order    = (string) $this->getState('forms_filter_order', 'name');
        $orderDir = strtoupper((string) $this->getState('forms_filter_order_Dir', 'DESC'));

        $allowedDir = ['ASC', 'DESC'];
        if (!in_array($orderDir, $allowedDir, true)) {
            $orderDir = 'DESC';
        }

        // ⚠️ idéalement whitelister les colonnes triables
        $query->order($db->escape($order) . ' ' . $orderDir);

        return $query;
    }

        /**
     * @return string The query
     */
    /*
    public function _buildQuery()
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
    }*/


    /**
     * Gets the currencies
     * @return array List of products
     */
    /*
    public function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_data;
    }

    public function getTotal()
    {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }

    public function getPagination()
    {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }*/

}


