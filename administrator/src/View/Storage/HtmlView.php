<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA + GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder\Administrator\View\Storage;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use CB\Component\Contentbuilder\Administrator\View\Contentbuilder\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $form;
    public $elements;
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
        $wa->getRegistry()->addExtensionRegistryFile('com_contentbuilder');
        $wa->useScript('com_contentbuilder.jscolor');

		if (!$this->frontend) {
            // 1️⃣ Récupération du WebAssetManager
            $document = $this->getDocument();
            $wa = $document->getWebAssetManager();
            $wa->addInlineStyle(
                '.icon-logo_left{
                    background-image:url(' . Uri::root(true) . '/media/com_contentbuilder/images/logo_left.png);
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

        $this->elements = [];
        $this->pagination = null;
        $this->state = null;

        try {
            $storageId  = (int) ($this->item->id ?? $app->input->getInt('id', 0));
            if ($storageId > 0) {
                $factory = $app->bootComponent('com_contentbuilder')->getMVCFactory();
                $elementsModel = $factory->createModel('Elements', 'Administrator');

                if (!$elementsModel) {
                    throw new \RuntimeException('Modèle Elements introuvable (factory)');
                }

                // IMPORTANT : fournir le form id au ListModel
                $elementsModel->setFormId($storageId);
                $elementsModel->setState('filter.form_id', $storageId);

                // Charge les items
                $this->elements   = $elementsModel->getItems();
                $this->pagination = $elementsModel->getPagination();
                $this->state      = $elementsModel->getState();
                $this->ordering   = ($this->state && $this->state->get('list.ordering') === 'ordering');
            }
        } catch (\Throwable $e) {
            $app->enqueueMessage(
                'Erreur lors du chargement des éléments : ' . $e->getMessage(),
                'warning'
            );
        }

        $isNew = ((int) ($this->item->id ?? 0) < 1);
        $text  = $isNew ? Text::_('COM_CONTENTBUILDER_NEW') : Text::_('COM_CONTENTBUILDER_EDIT');

        ToolbarHelper::title(
            'ContentBuilder :: ' . ($isNew ? Text::_('COM_CONTENTBUILDER_STORAGES') : ($this->item->title ?? ''))
            . ' : <small><small>[ ' . $text . ' ]</small></small>',
            'logo_left'
        );

        ToolbarHelper::apply('storage.apply');
        ToolbarHelper::save('storage.save');

        ToolbarHelper::custom('storage.save2new', 'save', '', Text::_('COM_CONTENTBUILDER_SAVENEW'), false);
        ToolbarHelper::publish('storage.publish');
        ToolbarHelper::unpublish('storage.unpublish');
        ToolbarHelper::deleteList(
            Text::_('COM_CONTENTBUILDER_DELETE_FIELDS_CONFIRM'),
            'storage.listDelete',
            Text::_('COM_CONTENTBUILDER_DELETE_FIELDS')
        );

        ToolbarHelper::cancel('storage.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
