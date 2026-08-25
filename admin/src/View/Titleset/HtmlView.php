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
use Joomla\CMS\Toolbar\Toolbar;

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
            /** @var Toolbar $toolbar */
            $toolbar = $this->getDocument()->getToolbar('toolbar');
            $toolbar->linkButton('titleset-duplicate')
                ->url(Route::_('index.php?option=com_contentbuilderng&view=titleset&filename=' . rawurlencode((string) $this->data['filename']) . '&source=provided&duplicate=1', false))
                ->text('COM_CONTENTBUILDERNG_TITLESETS_DUPLICATE')
                ->icon('icon-copy')
                ->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_DUPLICATE_DESC')]);
            $toolbar->linkButton('titleset-close')
                ->url(Route::_('index.php?option=com_contentbuilderng&view=titlesets', false))
                ->text('JTOOLBAR_CLOSE')
                ->icon('icon-cancel')
                ->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_CLOSE_DESC')]);
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
