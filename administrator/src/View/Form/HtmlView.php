<?php
/**
 * @package ContentBuilder
 * @author Markus Bopp / XDA+GIL
 * @link https://breezingforms.vcmb.fr
 * @copyright (C) 2026 by XDA+GIL
 * @license GNU/GPL
 */
namespace Component\Contentbuilder\Administrator\View\Form;

ini_set('display_errors', 1);
error_reporting(E_ALL);

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;

class HtmlView extends BaseHtmlView
{

    public function display($tpl = null)
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $document = Factory::getApplication()->getDocument();
        $document->addScript(Uri::root(true) . '/media/js/jscolor/jscolor.js');

        echo '<style type="text/css">
                .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
            </style>';
        echo '<link rel="stylesheet" href="' . Uri::root(true) . '/media/contentbuilder/css/bluestork.fix.css" type="text/css" />';

        // Formulaire principal
        $this->form = $this->get('Item');

        // Chargement sécurisé des éléments
        $formId = (int) $this->getModel()->getState('form.id'); // ou $this->input->getInt('id');

        if ($formId > 0) {
            $elementsModel = $this->getModel('Elements', 'Contentbuilder');
            if ($elementsModel === null) {
                Factory::getApplication()->enqueueMessage('Erreur : Modèle Elements non trouvé. Vérifiez src/Model/ElementsModel.php', 'error');
                $this->elements = [];
            } else {
                $this->elements = $elementsModel->getItems() ?? [];;
                $this->elementsPagination = $elementsModel->getPagination();
            }
        } else {
            $elementsModel = null;
            $this->elements = [];
            $this->elementsPagination = null;
        }
        
        $this->pagination = $elementsModel ? $elementsModel->getPagination() : null;
        $this->state = $elementsModel ? $elementsModel->getState() : null;

        $isNew = ($this->form->id < 1);
        $text = $isNew ? Text::_('COM_CONTENTBUILDER_NEW') : Text::_('COM_CONTENTBUILDER_EDIT');

        ToolBarHelper::title('ContentBuilder :: ' . ($isNew ? Text::_('COM_CONTENTBUILDER_FORM') : $this->form->name) . ' : <small><small>[ ' . $text . ' ]</small></small>', 'logo_left.png');

        ToolBarHelper::apply('form.apply');
        ToolBarHelper::save('form.save');
        ToolBarHelper::custom('form.save2New', 'save', '', Text::_('COM_CONTENTBUILDER_SAVENEW'), false);
        ToolBarHelper::custom('form.list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_LIST_INCLUDE'), false);
        ToolBarHelper::custom('form.no_list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_NO_LIST_INCLUDE'), false);
        ToolBarHelper::custom('form.editable', 'edit', '', Text::_('COM_CONTENTBUILDER_EDITABLE'), false);
        ToolBarHelper::custom('form.not_editable', 'edit', '', Text::_('COM_CONTENTBUILDER_NOT_EDITABLE'), false);
        ToolBarHelper::publish('form.publish', 'publish', '', Text::_('COM_CONTENTBUILDER_PUBLISH'), false);
        ToolBarHelper::unpublish('form.unpublish', 'unpublish', '', Text::_('COM_CONTENTBUILDER_UNPUBLISH'), false);

        if ($isNew) {
            ToolBarHelper::cancel('form.cancel', 'JTOOLBAR_CLOSE');
        } else {
            ToolBarHelper::cancel('form.cancel', 'Close');
        }

        // Compatibilité template
        $this->lists['order_Dir'] = $this->state ? $this->state->get('list.direction', 'asc') : 'asc';
        $this->lists['order'] = $this->state ? $this->state->get('list.ordering', 'ordering') : 'ordering';
        $this->lists['limitstart'] = $this->state ? $this->state->get('list.start', 0) : 0;
        $this->ordering = true;

        // Données additionnelles
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = 'SELECT CONCAT(REPEAT(\'..\', COUNT(parent.id) - 1), node.title) as text, node.id as value
                FROM #__usergroups AS node, #__usergroups AS parent
                WHERE node.lft BETWEEN parent.lft AND parent.rgt
                GROUP BY node.id ORDER BY node.lft';
        $db->setQuery($query);
        $this->gmap = $db->loadObjectList() ?? [];

        $this->form->config = $this->form->config ? unserialize(base64_decode($this->form->config)) : null;

        $this->list_states_action_plugins = $this->get('ListStatesActionPlugins') ?? [];
        $this->verification_plugins = $this->get('VerificationPlugins') ?? [];
        $this->theme_plugins = $this->get('ThemePlugins') ?? [];

        HTMLHelper::_('behavior.keepalive');
        parent::display($tpl);
    }
}