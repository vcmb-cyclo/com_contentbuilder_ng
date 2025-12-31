<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA+GIL
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\Storages;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

/**
 * Vue Storages pour ContentBuilder
 */
class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $lists;
    protected $ordering;

    /**
     * Méthode d'affichage de la vue
     *
     * @param   string  $tpl  Nom du template alternatif
     * @return  void
     */
    public function display($tpl = null)
    {
        // Récupération des données du modèle
        $this->items      = $this->get('Data');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // Préparation des filtres et tris
        $this->lists['order_Dir']   = $this->state->get('storages_filter_order_Dir');
        $this->lists['order']       = $this->state->get('storages_filter_order');
        $this->lists['state']       = HTMLHelper::_('grid.state', $this->state->get('storages_filter_state'));
        $this->ordering             = ($this->lists['order'] == 'ordering');

        // Vérification des erreurs
        if (count($errors = $this->get('Errors')))
        {
            throw new \Exception(implode('<br>', $errors), 500);
        }

        // Barre d'outils
        $this->addToolbar();

        // Ajout du CSS personnalisé (méthode propre)
        $this->addStylesheet();
        $this->addToolbarIcon();

        parent::display($tpl);
    }

    /**
     * Ajoute la barre d'outils
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(
            Text::_('COM_CONTENTBUILDER_STORAGES'),
            'contentbuilder icon-contentbuilder'  // classe CSS personnalisée
        );

        ToolbarHelper::addNew('storage.add');
        ToolbarHelper::editList('storage.edit');
        ToolbarHelper::deleteList('', 'storages.delete');
        ToolbarHelper::preferences('com_contentbuilder');
    }

    /**
     * Ajoute le CSS personnalisé
     */
    protected function addStylesheet()
    {
        // Chargement d'un CSS fixe pour bluestork si nécessaire (sinon à supprimer)
        $document = Factory::getDocument();
        $document->addStyleSheet(Uri::root(true) . '/administrator/components/com_contentbuilder/views/bluestork.fix.css');
    }

    /**
     * Ajoute l'icône personnalisée pour le titre de la barre d'outils
     */
    protected function addToolbarIcon()
    {
        // Récupération du WebAssetManager (méthode moderne)
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->useStyle('com_contentbuilder.admin-toolbar'); // à déclarer dans joomla.asset.json

        // Optionnel : si vous avez aussi bluestork.fix.css
        // $wa->useStyle('com_contentbuilder.bluestork-fix');
    }
}