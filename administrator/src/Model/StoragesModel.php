<?php

/**
 * ContentBuilder Storages Model (List).
 *
 * Handles CRUD and publish state for storage in the admin interface.
 *
 * @package     ContentBuilder
 * @subpackage  Administrator.Model
 * @author      Markus Bopp / XDA+GIL
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */

namespace CB\Component\Contentbuilder\Administrator\Model;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;
use Joomla\Utilities\ArrayHelper;
use CB\Component\Contentbuilder\Administrator\CBRequest;

class StoragesModel extends ListModel
{
    // Optionnel mais recommandé : définir le nom de la table (sans postfix)
    protected $table = 'Storage';

    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'a.id',
                'id',
                'a.name',
                'name',
                'a.title',
                'title',
                'a.display_in',
                'display_in',
                'a.published',
                'published'
            ];
        }

        $this->option = 'com_contentbuilder';

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        // ✅ appels standard StorageModel
        parent::populateState($ordering, $direction);

        // ✅ tes filtres custom, mais stockés dans l’état
        $filterState = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd');
        $this->setState('filter.state', $filterState);
    }


    protected function getListQuery(): QueryInterface
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Base query
        $query->select('a.*')
            ->from($db->quoteName('#__contentbuilder_storages', 'a'));

        // Published filter (storages_filter_state: 'P' or 'U')
        $filterState = (string) $this->getState('storages_filter_state');

        if ($filterState === 'P' || $filterState === 'U') {
            $published = ($filterState === 'P') ? 1 : 0;
            $query->where($db->quoteName('a.published') . ' = ' . (int) $published);
        }

        // Ordering (equivalent à ton buildOrderBy())
        $ordering  = (string) $this->getState('list.ordering', 'a.id');
        $direction = strtoupper((string) $this->getState('list.direction', 'DESC'));

        // Petite sécurité sur la direction
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'DESC';
        }

        // Optionnel : whitelist rapide des colonnes triables
        $allowedOrdering = ['a.id', 'a.title', 'a.published', 'a.created', 'a.ordering'];
        if (!in_array($ordering, $allowedOrdering, true)) {
            $ordering = 'a.id';
        }

        $query->order($db->escape($ordering . ' ' . $direction));

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

        $pks = array_values(array_filter($pks));
        if (!$pks) {
            return false;
        }

        $factory = Factory::getApplication()
            ->bootComponent('com_contentbuilder')
            ->getMVCFactory();

        $formModel = $factory->createModel('storage', 'Administrator', ['ignore_request' => true]);

        if (!$formModel) {
            $this->setError('Unable to create Storage model');
            return false;
        }

        if (!$formModel->delete($pks)) {
            $this->setError($formModel->getError());
            return false;
        }

        return true;
    }

    /*
    function setPublished()
    {
        $cids = CBRequest::getVar('cid', array(), '', 'array');
        ArrayHelper::toInteger($cids);
        $this->getDatabase()->setQuery(' Update #__contentbuilder_storages ' .
            '  Set published = 1 Where id In ( ' . implode(',', $cids) . ')');
        $this->getDatabase()->execute();

    }

    function setUnpublished()
    {
        $cids = CBRequest::getVar('cid', array(), '', 'array');
        ArrayHelper::toInteger($cids);
        $this->getDatabase()->setQuery(' Update #__contentbuilder_storages ' .
            '  Set published = 0 Where id In ( ' . implode(',', $cids) . ')');
        $this->getDatabase()->execute();
    }*/

    /*
     *
     * MAIN LIST AREA
     * 
     */
/*
    private function buildOrderBy()
    {
        $mainframe = Factory::getApplication();
        $option = 'com_contentbuilder';

        $orderby = '';
        $filter_order = $this->getState('storages_filter_order');
        $filter_order_Dir = $this->getState('storages_filter_order_Dir');

        // Error handling is never a bad thing.
        if (!empty($filter_order) && !empty($filter_order_Dir)) {
            $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
        }

        return $orderby;
    }
*/

    /**
     * @return string The query
     */
    /*
    private function _buildQuery()
    {
        $where = '';

        // PUBLISHED FILTER SELECTED?
        $filter_state = '';
        if ($this->getState('storages_filter_state') == 'P' || $this->getState('storages_filter_state') == 'U') {
            $published = 0;
            if ($this->getState('storages_filter_state') == 'P') {
                $published = 1;
            }

            $and = ' And';

            $filter_state .= ' published = ' . $published;
        }

        if ($filter_state != '') {
            $where = ' Where ';
        }

        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_storages ' . $where . $filter_state . $this->buildOrderBy();
    }*/


    function saveOrder()
    {
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);

        $total = count($items);
        $row = $this->getTable('Storage');
        $groupings = array();

        $order = CBRequest::getVar('order', array(), 'post', 'array');
        ArrayHelper::toInteger($order);

        // update ordering values
        for ($i = 0; $i < $total; $i++) {
            $row->load($items[$i]);
            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];
                if (!$row->save()) {
                    $this->setError($row->getError());
                    return false;
                }
            } // if
        } // for


        $row->reorder();
    }

    /**
     * Gets the currencies
     * @return array List of products
     */
    /*    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_data;
    }
*/
}
