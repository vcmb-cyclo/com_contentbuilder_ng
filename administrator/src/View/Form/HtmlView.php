<?php

/**
 * @package ContentBuilder
 * @author Markus Bopp / XDA+GIL
 * @link https://breezingforms.vcmb.fr
 * @copyright (C) 2026 by XDA+GIL
 * @license GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\Form;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;
use CB\Component\Contentbuilder\Administrator\View\Contentbuilder\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $app->input->set('hidemainmenu', true);

        // JS
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->useScript('com_contentbuilder.jscolor');

        // Formulaire JForm
        $this->form = $this->getModel()->getForm();

        // Données (l’item)
        $this->item = $this->getModel()->getItem();

        // Chargement sécurisé des éléments
        $formId = (int) ($this->item->id ?? $app->input->getInt('id', 0));

        $this->elements = [];
        $this->pagination = null;
        $this->state = null;

        try {
            $formId = (int) ($formId ?? 0);
            if ($formId > 0) {
                $factory = $app->bootComponent('com_contentbuilder')->getMVCFactory();
                $elementsModel = $factory->createModel('Elements', 'Administrator');

                if (!$elementsModel) {
                    throw new \RuntimeException('Modèle Elements introuvable (factory)');
                }

                // IMPORTANT : fournir le form id au ListModel
                $elementsModel->setFormId($formId);

                // charge les items
                $this->elements   = $elementsModel->getItems();
                $this->pagination = $elementsModel->getPagination();
                $this->state      = $elementsModel->getState();
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage(
                'Erreur lors du chargement des éléments : ' . $e->getMessage(),
                'warning'
            );
        }

        $isNew = ($formId < 1);
        $text  = $isNew ? Text::_('COM_CONTENTBUILDER_NEW') : Text::_('COM_CONTENTBUILDER_EDIT');

        ToolbarHelper::title(
            'ContentBuilder :: ' . ($isNew ? Text::_('COM_CONTENTBUILDER_FORM') : ($this->item->name ?? '')) .
                ' : <small><small>[ ' . $text . ' ]</small></small>',
            'logo_left.png'
        );

        ToolbarHelper::apply('form.apply');
        ToolbarHelper::save('form.save');
        ToolbarHelper::save2new('form.save2new', 'save', '', Text::_('COM_CONTENTBUILDER_SAVENEW'), false);

        ToolbarHelper::custom('form.list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_LIST_INCLUDE'), false);
        ToolbarHelper::custom('form.no_list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_NO_LIST_INCLUDE'), false);

        ToolbarHelper::custom('form.editable', 'edit', '', Text::_('COM_CONTENTBUILDER_EDITABLE'), false);
        ToolbarHelper::custom('form.not_editable', 'edit', '', Text::_('COM_CONTENTBUILDER_NOT_EDITABLE'), false);

        ToolbarHelper::publish('form.publish', 'publish', '', Text::_('COM_CONTENTBUILDER_PUBLISH'), false);
        ToolbarHelper::unpublish('form.unpublish', 'unpublish', '', Text::_('COM_CONTENTBUILDER_UNPUBLISH'), false);

        ToolbarHelper::cancel('form.cancel', $isNew ? 'JTOOLBAR_CLOSE' : 'JTOOLBAR_CLOSE');

        // Compat template / listes
        $listOrder = $this->state?->get('list.ordering', 'ordering') ?? 'ordering';
        $listDirn  = $this->state?->get('list.direction', 'asc') ?? 'asc';

        // ordering actif seulement si on est trié par "ordering"
        $this->ordering = ($listOrder === 'ordering');


        // Données additionnelles
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $q = $db->getQuery(true)
            ->select("CONCAT(REPEAT('..', COUNT(parent.id) - 1), node.title) AS " . $db->quoteName('text') . ", node.id AS " . $db->quoteName('value'))
            ->from($db->quoteName('#__usergroups', 'node'))
            ->from($db->quoteName('#__usergroups', 'parent'))
            ->where('node.lft BETWEEN parent.lft AND parent.rgt')
            ->group('node.id')
            ->order('node.lft');

        $db->setQuery($q);
        $this->gmap = $db->loadObjectList() ?? [];


        // ✅ Décode config de manière robuste (très probable cause ids 9/11)
        $this->item->config = $this->decodeLegacyConfig($this->item->config ?? null);

        $this->list_states_action_plugins = $this->get('ListStatesActionPlugins') ?? [];
        $this->verification_plugins       = $this->get('VerificationPlugins') ?? [];
        $this->theme_plugins              = $this->get('ThemePlugins') ?? [];

        HTMLHelper::_('behavior.keepalive');
        $this->setLayout('edit');
        parent::display($tpl);
    }

    /**
     * Décode l'ancien format base64 + serialize, de façon tolérante.
     * Retourne array|null sans faire planter l'édition si la donnée est corrompue.
     */
    private function decodeLegacyConfig($raw): ?array
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        // Base64 strict
        $decoded = base64_decode((string) $raw, true);
        if ($decoded === false) {
            // Donnée pas en base64 => on évite de casser l'édition
            return null;
        }

        // Unserialize sécurisé (pas d'objets)
        try {
            $data = @unserialize($decoded, ['allowed_classes' => false]);
        } catch (\Throwable $e) {
            return null;
        }

        // Unserialize peut retourner false si corrompu (sauf cas b:0;)
        if ($data === false && $decoded !== 'b:0;') {
            return null;
        }

        return is_array($data) ? $data : null;
    }
}
