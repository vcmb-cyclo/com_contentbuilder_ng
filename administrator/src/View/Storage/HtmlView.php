<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder_ng\Administrator\View\Storage;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use CB\Component\Contentbuilder_ng\Administrator\View\Contentbuilder_ng\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $form;
    public $fields;
    public $tables;
    public $pagination;
    public $ordering;
    public $item;
    public $state;
    public bool $frontend = false;

    public function display($tpl = null): void
    {         
        $app = Factory::getApplication();
        $app->input->set('hidemainmenu', true);

        // JS
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_contentbuilder_ng');
        $wa->useScript('com_contentbuilder_ng.jscolor');

		if (!$this->frontend) {
            // 1️⃣ Récupération du WebAssetManager
            $document = $this->getDocument();
            $wa = $document->getWebAssetManager();
            $wa->addInlineStyle(
                '.icon-logo_left{
                    background-image:url(' . Uri::root(true) . '/media/com_contentbuilder_ng/images/logo_left.png);
                    background-size:contain;
                    background-repeat:no-repeat;
                    background-position:center;
                    display:inline-block;
                    width:48px;
                    height:48px;
                }'
            );
        }    
            
        // Formulaire JForm
        $this->form = $this->getModel()->getForm();

        // Données (l’item)
        $this->item = $this->getModel()->getItem();

        $this->tables     = $this->get('DbTables');

        // Chargement sécurisé des éléments
        $storageId = (int) ($this->item->id ?? $app->input->getInt('id', 0));

        $this->fields = [];
        $this->pagination = null;
        $this->state = null;

        try {
            $storageId  = (int) ($this->item->id ?? $app->input->getInt('id', 0));
            if ($storageId > 0) {
                $factory = $app->bootComponent('com_contentbuilder_ng')->getMVCFactory();
                $fieldsModel = $factory->createModel('Storagefields', 'Administrator');

                if (!$fieldsModel) {
                    throw new \RuntimeException('Modèle Storagefields introuvable (factory)');
                }

                // IMPORTANT : fournir le form id au ListModel
                $fieldsModel->setStorageId($storageId);

                $list = (array) $app->input->get('list', [], 'array');
                $ordering = isset($list['ordering']) ? preg_replace('/[^a-zA-Z0-9_\\.]/', '', (string) $list['ordering']) : '';
                $direction = isset($list['direction']) ? strtolower((string) $list['direction']) : '';
                $start = isset($list['start']) ? (int) $list['start'] : null;
                $limit = isset($list['limit']) ? (int) $list['limit'] : null;

                if ($ordering !== '') {
                    $fieldsModel->setState('list.ordering', $ordering);
                }
                if ($direction === 'asc' || $direction === 'desc') {
                    $fieldsModel->setState('list.direction', $direction);
                }
                if ($start !== null) {
                    $fieldsModel->setState('list.start', $start);
                }
                if ($limit !== null && $limit >= 0) {
                    $fieldsModel->setState('list.limit', $limit);
                }

                // Charge les items
                $this->fields     = $fieldsModel->getItems();
                $this->pagination = $fieldsModel->getPagination();
                $this->state      = $fieldsModel->getState();
                $this->ordering   = ($this->state && $this->state->get('list.ordering') === 'ordering');
            }
        } catch (\Throwable $e) {
            $app->enqueueMessage(
                'Erreur lors du chargement des champs : ' . $e->getMessage(),
                'warning'
            );
        }

        $isNew = ((int) ($this->item->id ?? 0) < 1);
        $text  = $isNew ? Text::_('COM_CONTENTBUILDER_NG_NEW') : Text::_('COM_CONTENTBUILDER_NG_EDIT');

        ToolbarHelper::title(
            Text::_('COM_CONTENTBUILDER_NG') .' :: ' . ($isNew ? Text::_('COM_CONTENTBUILDER_NG_STORAGES') : ($this->item->title ?? ''))
            . ' : <small><small>[ ' . $text . ' ]</small></small>',
            'logo_left'
        );

        ToolbarHelper::apply('storage.apply');
        ToolbarHelper::save('storage.save');

        ToolbarHelper::custom('storage.save2new', 'save', '', Text::_('COM_CONTENTBUILDER_NG_SAVENEW'), false);
        ToolbarHelper::publish('storage.publish');
        ToolbarHelper::unpublish('storage.unpublish');

        $id = (int) ($this->item->id ?? 0);

        if ($id > 0) {
            // POST via task + token : on fait pointer vers une URL, mais le bouton doit poster (sinon token)
            // Astuce: utiliser un "custom" button JS submitTask
            ToolbarHelper::custom('datatable.create', 'database', '', Text::_('COM_CONTENTBUILDER_NG_DATATABLE_CREATE'), false);
            ToolbarHelper::custom('datatable.sync', 'refresh', '', Text::_('COM_CONTENTBUILDER_NG_DATATABLE_SYNC'), false);

            $createTip = json_encode(Text::_('COM_CONTENTBUILDER_NG_DATATABLE_CREATE_TIP'), JSON_UNESCAPED_UNICODE);
            $syncTip   = json_encode(Text::_('COM_CONTENTBUILDER_NG_DATATABLE_SYNC_TIP'), JSON_UNESCAPED_UNICODE);

            $wa->addInlineScript(
                "(function () {
                    function getButton(task) {
                        return document.querySelector('[data-task=\"' + task + '\"]')
                            || document.querySelector('[onclick*=\"' + task + '\"]');
                    }

                    function applyTooltip(task, message) {
                        var button = getButton(task);
                        if (!button || !message) {
                            return;
                        }
                        button.setAttribute('title', message);
                        button.setAttribute('data-bs-title', message);
                        button.setAttribute('data-bs-toggle', 'tooltip');
                        button.setAttribute('data-bs-placement', 'bottom');

                        if (window.bootstrap && window.bootstrap.Tooltip) {
                            window.bootstrap.Tooltip.getOrCreateInstance(button);
                        }
                    }

                    function init() {
                        applyTooltip('datatable.create', " . $createTip . ");
                        applyTooltip('datatable.sync', " . $syncTip . ");
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', init, { once: true });
                    } else {
                        init();
                    }
                }());"
            );
        }
        
        ToolbarHelper::deleteList(
            Text::_('COM_CONTENTBUILDER_NG_DELETE_FIELDS_CONFIRM'),
            'storage.listDelete',
            Text::_('COM_CONTENTBUILDER_NG_DELETE_FIELDS')
        );

        ToolbarHelper::cancel('storage.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
