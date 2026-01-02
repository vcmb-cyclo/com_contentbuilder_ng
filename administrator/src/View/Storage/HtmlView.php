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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $form;
    public $elements;
    public $tables;
    public $pagination;
    public $ordering;

    public function display($tpl = null): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $document = Factory::getApplication()->getDocument();
        $document->addScript(Uri::root(true) . '/administrator/components/com_contentbuilder/assets/js/jscolor/jscolor.js');

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
        ToolbarHelper::custom('storage.listpublish', 'publish', '', Text::_('COM_CONTENTBUILDER_PUBLISH'), false);
        ToolbarHelper::custom('storage.listunpublish', 'unpublish', '', Text::_('COM_CONTENTBUILDER_UNPUBLISH'), false);
        ToolbarHelper::custom('storage.listdelete', 'delete', '', Text::_('COM_CONTENTBUILDER_DELETE_FIELDS'), false);

        ToolbarHelper::cancel('storage.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}

