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

    public function display($tpl = null): void
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
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage(
                'Erreur lors du chargement des éléments : ' . $e->getMessage(),
                'warning'
            );
        }

        $isNew = ((int) ($this->item->id ?? 0) < 1);
        $text  = $isNew ? Text::_('COM_CONTENTBUILDER_NEW') : Text::_('COM_CONTENTBUILDER_EDIT');

        ToolbarHelper::title(
            'ContentBuilder :: ' . ($isNew ? Text::_('COM_CONTENTBUILDER_STORAGES') : ($this->item->title ?? ''))
            . ' : <small><small>[ ' . $text . ' ]</small></small>',
            'logo_left.png'
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

