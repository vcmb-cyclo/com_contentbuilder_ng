<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\View\Titleset;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Model\TitlesetModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

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

        $isProvided = ($this->data['source'] ?? '') === 'provided';
        ToolbarHelper::title(Text::_($isProvided
            ? 'COM_CONTENTBUILDERNG_TITLESETS_VIEW_TITLE'
            : 'COM_CONTENTBUILDERNG_TITLESETS_EDIT_TITLE'), $isProvided ? 'eye' : 'edit');
        if ($isProvided) {
            ToolbarHelper::link(
                Route::_('index.php?option=com_contentbuilderng&view=titleset&filename=' . rawurlencode((string) $this->data['filename']) . '&source=provided&duplicate=1', false),
                'COM_CONTENTBUILDERNG_TITLESETS_DUPLICATE',
                'copy'
            );
            ToolbarHelper::cancel('titleset.cancel');
            parent::display($tpl);
            return;
        }
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
