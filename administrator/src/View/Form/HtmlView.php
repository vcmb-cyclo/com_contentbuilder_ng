<?php

/**
 * @package ContentBuilder
 * @author Markus Bopp / XDA+GIL
 * @link https://breezingforms.vcmb.fr
 * @copyright (C) 2026 by XDA+GIL
 * @license GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Administrator\View\Form;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use CB\Component\Contentbuilder_ng\Administrator\View\Contentbuilder_ng\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $app->input->set('hidemainmenu', true);

        // JS
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->useScript('com_contentbuilder_ng.jscolor');

        $wa->addInlineStyle(
            '.icon-48-logo_icon_cb{background-image:url('
            . Uri::root(true)
            . '/media/com_contentbuilder_ng/images/logo_icon_cb.png);background-size:contain;background-repeat:no-repeat;}'
        );


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
                $factory = $app->bootComponent('com_contentbuilder_ng')->getMVCFactory();
                $elementsModel = $factory->createModel('Elements', 'Administrator');

                if (!$elementsModel) {
                    throw new \RuntimeException('Modèle Elements introuvable (factory)');
                }

                // IMPORTANT : fournir le form id au ListModel
                $elementsModel->setFormId($formId);

                // Charge les items
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
        $text  = $isNew ? Text::_('COM_CONTENTBUILDER_NG_NEW') : Text::_('COM_CONTENTBUILDER_NG_EDIT');

        ToolbarHelper::title(
            Text::_('COM_CONTENTBUILDER_NG') .' :: ' . ($isNew ? Text::_('COM_CONTENTBUILDER_NG_FORM') : ($this->item->name ?? '')) .
                ' : <small><small>[ ' . $text . ' ]</small></small>',
            'logo_left'
        );

        ToolbarHelper::apply('form.apply');
        ToolbarHelper::save('form.save');
        ToolbarHelper::save2new('form.save2new');

        ToolbarHelper::custom('form.list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_NG_LIST_INCLUDE'), false);
        ToolbarHelper::custom('form.no_list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_NG_NO_LIST_INCLUDE'), false);

        ToolbarHelper::custom('form.editable', 'edit', '', Text::_('COM_CONTENTBUILDER_NG_EDITABLE'), false);
        ToolbarHelper::custom('form.not_editable', 'edit', '', Text::_('COM_CONTENTBUILDER_NG_NOT_EDITABLE'), false);

        ToolbarHelper::publish('forms.publish');
        ToolbarHelper::unpublish('forms.unpublish');

        ToolbarHelper::cancel('form.cancel', $isNew ? 'JTOOLBAR_CLOSE' : 'JTOOLBAR_CLOSE');

        if ($formId > 0) {
            $previewUntil = time() + 600;
            $previewPayload = $formId . '|' . $previewUntil;
            $previewSig = hash_hmac('sha256', $previewPayload, (string) $app->get('secret'));
            $previewUrl = Uri::root()
                . 'index.php?option=com_contentbuilder_ng&task=edit.display&id='
                . $formId
                . '&record_id=0'
                . '&cb_preview=1'
                . '&cb_preview_until=' . $previewUntil
                . '&cb_preview_sig=' . $previewSig;
            Toolbar::getInstance('toolbar')->appendButton(
                'Link',
                'eye',
                Text::_('COM_CONTENTBUILDER_NG_PREVIEW'),
                $previewUrl,
                '_blank'
            );
        }

        // Compat template / listes
        $this->listOrder = (string) $this->state?->get('list.ordering', 'a.ordering');
        $this->listDirn  = (string) $this->state?->get('list.direction', 'ASC');

        $lists['order']     = $this->listOrder;
        $lists['order_Dir'] = $this->listDirn;

        // ordering actif seulement si tri par ordering
        $this->ordering = in_array($this->listOrder, ['ordering', 'a.ordering'], true);


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
