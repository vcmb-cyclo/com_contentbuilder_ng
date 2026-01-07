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

    // Optionnel mais recommandé : définir le nom de la table
    protected $table = 'Form'; // Nom de la classe Table (sans prefix)
    
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
}


