<?php

/**
 * ContentBuilder Elements Model.
 *
 * Handles CRUD and publish state for element in the admin interface.
 *
 * @package     ContentBuilder
 * @subpackage  Administrator.Model
 * @author      Xavier DANO
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */


namespace CB\Component\Contentbuilder\Administrator\Model;

\defined('_JEXEC') or die;

ini_set('display_errors', 1);
error_reporting(E_ALL);

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseQuery;

class ElementsModel extends ListModel
{
    /**
     * ID du formulaire courant (form_id)
     */
    protected int $formId = 0;

    /**
     * Constructor.
     */
    public function __construct($config = [])
    {
        // Colonnes autorisées pour le filtrage et le tri (très important pour la sécurité)
        $this->filter_fields = [
            'id',
            'type',
            'label',
            'published',
            'linkable',
            'editable',
            'list_include',
            'search_include',
            'ordering',
            'order_type'
        ];

        parent::__construct($config);
    }


    /**
     * Méthode pour initialiser les états (filtres, pagination, tri)
     */
    protected function populateState($ordering = 'ordering', $direction = 'asc')
    {
        $app = Factory::getApplication();

        // Récupération du form_id depuis l'input (obligatoire pour cette vue)
        $formId = $app->input->getInt('id', 0);
        $this->setState('form.id', $formId);
        $this->formId = $formId;

        // Filtre sur published
        $published = $app->getUserStateFromRequest('com_contentbuilder.elements.filter.published', 'filter_published', '', 'string');
        $this->setState('filter.published', $published);

        // Recherche (si tu veux ajouter un champ de recherche sur label ou type)
        $search = $app->getUserStateFromRequest('com_contentbuilder.elements.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        // Pagination
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
        $this->setState('list.limit', $limit);

        $value = $app->getUserStateFromRequest('com_contentbuilder.elements.limitstart', 'limitstart', 0, 'int');
        $limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
        $this->setState('list.start', $limitstart);

        // Tri
        parent::populateState($ordering, $direction);
    }

    /**
     * Construction de la requête pour récupérer la liste des éléments
     */

    protected function getListQuery(): DatabaseQuery
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Sélectionner les colonnes pertinentes de #__contentbuilder_elements
        $query->select(
            $db->quoteName([
                'id',
                'form_id',
                'reference_id',
                'type',
                'change_type',
                'options',
                'custom_init_script',
                'custom_action_script',
                'custom_validation_script',
                'validation_message',
                'default_value',
                'hint',
                'label',
                'list_include',
                'search_include',
                'item_wrapper',
                'wordwrap',
                'linkable',
                'editable',
                'validations',
                'published',
                'order_type',
                'ordering'
            ])
        )
            ->from($db->quoteName('#__contentbuilder_elements'))
            ->where($db->quoteName('form_id') . ' = ' . (int) $this->getState('form.id'));  // Filtre par form_id

        // Filtre publié (si défini)
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where($db->quoteName('published') . ' = ' . (int) $published);
        }

        // Recherche sur le label ou le type (si défini)
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(' . $db->quoteName('label') . ' LIKE ' . $search .
                ' OR ' . $db->quoteName('type') . ' LIKE ' . $search . ')');
        }

        // Tri sécurisé (grâce à filter_fields)
        $orderCol  = $this->getState('list.ordering', 'ordering');
        $orderDirn = $this->getState('list.direction', 'asc');

        // Application du tri (tri principal + ordre de secours sur "ordering")
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn) . ', ' . $db->escape('ordering') . ' ASC');

        return $query;
    }


    /**
     * Méthode pour réordonner les éléments (utilisée par listorderup/down/saveorder)
     */
    public function reorder($pks = null, $delta = 0, $where = '')
    {
        $table = Table::getInstance('ElementOption', 'CB\\Component\\Contentbuilder\\Administrator\\Table\\');

        $condition = 'form_id = ' . (int) $this->formId;

        return $table->reorder($condition);
    }

    /**
     * Méthode pour sauvegarder l'ordre (task listsaveorder)
     */
    public function saveorder($pks = null, $order = null)
    {
        $table = Table::getInstance('ElementOption', 'CB\\Component\\Contentbuilder\\Administrator\\Table\\');

        $conditions = ['form_id = ' . (int) $this->formId];

        return $table->saveorder($pks, $order, $conditions);
    }

    /**
     * Optionnel : méthode pour déplacer un élément (listorderup/down)
     */
    public function move($direction)
    {
        $table = Table::getInstance('ElementOption', 'CB\\Component\\Contentbuilder\\Administrator\\Table\\');

        if (!$table->load($this->getState('element.id'))) {
            return false;
        }

        return $table->move($direction, 'form_id = ' . (int) $this->formId);
    }

    private function buildOrderBy()
    {
        $mainframe = Factory::getApplication();
        $option = 'com_contentbuilder';

        $orderby = '';
        $filter_order = $this->getState('elements_filter_order');
        $filter_order_Dir = $this->getState('elements_filter_order_Dir');

        /* Error handling is never a bad thing*/
        if (!empty($filter_order) && !empty($filter_order_Dir)) {
            $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , ordering ';
        } else {
            $orderby = ' ORDER BY ordering ';
        }

        return $orderby;
    }


    function _buildQuery()
    {
        $filter_state = '';
        if ($this->getState('elements_filter_state') == 'P' || $this->getState('elements_filter_state') == 'U') {
            $published = 0;
            if ($this->getState('elements_filter_state') == 'P') {
                $published = 1;
            }

            $filter_state .= ' And published = ' . $published;
        }

        return "Select * From #__contentbuilder_elements Where form_id = " . $this->_id . $filter_state . $this->buildOrderBy();
    }

    /*
    function getData()
    {
        $this->_db->setQuery($this->_buildQuery(), $this->getState('limitstart'), $this->getState('limit'));
        $entries = $this->_db->loadObjectList();

        return $entries;
    }*/

    /**
     * Retourne le nombre de pages d'éléments (utilisé pour la pagination dans l'interface)
     * À adapter selon la logique originale (souvent basé sur le total d'éléments)
     */
    public function getPagesCounter()
    {
        // Exemple simple : total d'éléments / limite par page, arrondi au supérieur
        $total = $this->getTotal(); // Méthode héritée de ListModel
        $limit = $this->getState('list.limit', 10);
        if ($limit == 0) {
            return 1;
        }
        return (int) ceil($total / $limit);
    }
}
