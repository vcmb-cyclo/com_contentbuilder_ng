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
use CB\Component\Contentbuilder\Administrator\View\Contentbuilder\CBHtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $form;
    public $elements;
    public $tables;
    public $pagination;
    public $ordering;

    public function display($tpl = null): void
    {         
        $app = Factory::getApplication();
        $app->input->set('hidemainmenu', true);

        // JS
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->useScript('com_contentbuilder.jscolor');

        $this->tables     = $this->get('DbTables');
        $this->form       = $this->get('Storage');
        $this->elements   = $this->get('Data');
        $this->pagination = $this->get('Pagination');

        $isNew = ((int) ($this->form->id ?? 0) < 1);
        $text  = $isNew ? Text::_('COM_CONTENTBUILDER_NEW') : Text::_('COM_CONTENTBUILDER_EDIT');

        ToolbarHelper::title(
            'ContentBuilder :: ' . ($isNew ? Text::_('COM_CONTENTBUILDER_STORAGES') : ($this->form->title ?? ''))
            . ' : <small><small>[ ' . $text . ' ]</small></small>',
            'logo_left.png'
        );

        ToolbarHelper::apply('storage.apply');
        ToolbarHelper::save('storage.save');

        ToolbarHelper::custom('storage.save2New', 'save', '', Text::_('COM_CONTENTBUILDER_SAVENEW'), false);
        ToolbarHelper::publish('storage.publish', 'publish', '', Text::_('COM_CONTENTBUILDER_PUBLISH'), false);
        ToolbarHelper::unpublish('storage.unpublish', 'unpublish', '', Text::_('COM_CONTENTBUILDER_UNPUBLISH'), false);
        ToolbarHelper::unpublish('storage.listdelete', 'delete', '', Text::_('COM_CONTENTBUILDER_DELETE_FIELDS'), false);

        ToolbarHelper::cancel('storage.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}

