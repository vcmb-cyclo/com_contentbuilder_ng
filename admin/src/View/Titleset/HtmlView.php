<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\View\Titleset;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use CB\Component\Contentbuilderng\Administrator\Model\TitlesetModel;

final class HtmlView extends BaseHtmlView
{
    protected Form $form;
    protected array $data = [];

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        if (!$app->getIdentity()->authorise('core.manage', 'com_contentbuilderng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel();
        if (!$model instanceof TitlesetModel) {
            throw new \RuntimeException('Unexpected title set model.');
        }
        $this->data = $model->getData();
        $this->form = $model->getForm();

        ToolbarHelper::title(Text::_('COM_CONTENTBUILDERNG_TITLESETS_EDIT_TITLE'), 'edit');
        ToolbarHelper::apply('titleset.apply');
        ToolbarHelper::save('titleset.save');
        ToolbarHelper::custom(
            'titleset.validateFile',
            'check',
            'check',
            'COM_CONTENTBUILDERNG_TITLESETS_VALIDATE',
            false
        );
        if (($this->data['source'] ?? '') === 'custom' && ($this->data['filename'] ?? '') !== '') {
            ToolbarHelper::deleteList(
                Text::_('COM_CONTENTBUILDERNG_TITLESETS_DELETE_CONFIRM'),
                'titleset.deleteFile'
            );
        }
        ToolbarHelper::cancel('titleset.cancel');

        parent::display($tpl);
    }
}
