<?php

/**
 * ContentBuilder Forms Model (List).
 *
 * Handles CRUD and publish state for form in the admin interface.
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
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

class FormsModel extends ListModel
{
    public function __construct(
        $config,
        MVCFactoryInterface $factory
    ) {
        // IMPORTANT : on transmet factory/app/input à ListModel
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'a.id',
                'a.name',
                'a.tag',
                'a.title',
                'a.type',
                'a.display_in',
                'a.published',
                'a.modified'
            ];
        }

        parent::__construct($config, $factory);
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
        $db    = $this->getDatabase();          // Joomla 4/5/6
        $query = $db->getQuery(true);

        // Base query
        $query->select('a.*')
            ->from($db->quoteName('#__contentbuilder_forms', 'a'));

        // Published filter (filter.state : 'P' or 'U')
        $filterState = (string) $this->getState('filter.state');

        if ($filterState === 'P' || $filterState === 'U') {
            $published = ($filterState === 'P') ? 1 : 0;
            $query->where($db->quoteName('a.published') . ' = ' . (int) $published);
        }

        // Tag filter.
        $filterTag = (string) $this->getState('filter.tag');
        if ($filterTag !== '') {
            $query->where($db->quoteName('a.tag') . ' = ' . $db->quote($filterTag));
        }

        // Ordering (equivalent à ton buildOrderBy())
        $ordering  = (string) $this->getState('list.ordering', 'a.id');
        $direction = strtoupper((string) $this->getState('list.direction', 'DESC'));

        // Petite sécurité sur la direction
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'DESC';
        }

        // Optionnel : whitelist rapide des colonnes triables
        $allowedOrdering = ['a.id', 'a.name', 'a.tag', 'a.title', 'a.type', 'a.display_in', 'a.published', 'a.created', 'a.modified', 'a.ordering'];
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

        $formModel = $factory->createModel('form', 'Administrator', ['ignore_request' => true]);

        if (!$formModel) {
            $this->setError('Unable to create Form model');
            return false;
        }

        if (!$formModel->delete($pks)) {
            $this->setError($formModel->getError());
            return false;
        }

        return true;
    }


    /*
     *
     * MAIN LIST AREA
     * 
     */

    // Tag non standard.
    public function getTags()
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('tag') . ' AS ' . $db->quoteName('tag'))
            ->from($db->quoteName('#__contentbuilder_forms'))
            ->where($db->quoteName('tag') . ' <> ' . $db->quote(''))
            ->order($db->quoteName('tag') . ' ASC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}
