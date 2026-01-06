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
use Joomla\CMS\MVC\Model\ListModel;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use Joomla\Database\QueryInterface;

class FormsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'a.id', 'id',
                'a.name', 'name',
                'a.tag', 'tag',
                'a.title', 'title',
                'a.type', 'type',
                'a.display_in', 'display_in',
                'a.published', 'published'
            ];
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        // ✅ appels standard ListModel
        parent::populateState($ordering, $direction);

        // ✅ tes filtres custom, mais stockés dans l’état
        $filterState = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd');
        $this->setState('filter.state', $filterState);

        $filterTag = $app->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '', 'string');
        $this->setState('filter.tag', $filterTag);
    }

    protected function getListQuery(): QueryInterface
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->from($db->quoteName('#__contentbuilder_forms', 'a'));

        // Filtre published
        $filterState = (string) $this->getState('filter.state', '');
        if ($filterState === 'P') {
            $query->where($db->quoteName('a.published') . ' = 1');
        } elseif ($filterState === 'U') {
            $query->where($db->quoteName('a.published') . ' = 0');
        }

        // Filtre tag
        $tag = (string) $this->getState('filter.tag', '');
        if ($tag !== '') {
            $query->where('LOWER(' . $db->quoteName('a.tag') . ') LIKE ' . $db->quote('%' . strtolower($tag) . '%'));
        }

        // ✅ tri standard piloté par list.ordering/list.direction
        $orderCol  = (string) $this->getState('list.ordering', 'a.ordering');
        $orderDir = strtoupper((string) $this->getState('list.direction', 'ASC'));

        // Sécurise la direction
        if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
            $orderDir = 'ASC';
        }

        // Sécurise la colonne (Joomla la valide via filter_fields, mais on recheck)
        $allowed = $this->filter_fields ?? [];
        if (!in_array($orderCol, $allowed, true)) {
            $orderCol = 'a.ordering';
        }

        $query->order($db->escape($orderCol) . ' ' . $orderDir);

        return $query;
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
/*
    public function buildOrderBy()
    {
        $mainframe = Factory::getApplication();
        $option = 'com_contentbuilder';

        $orderby = '';
        $filter_order = $this->getState('forms_filter_order');
        $filter_order_Dir = $this->getState('forms_filter_order_Dir');

        // Error handling is never a bad thing.
        if (!empty($filter_order) && !empty($filter_order_Dir)) {
            $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
        }

        return $orderby;
    }*/

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

    // Tag non standard.
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


