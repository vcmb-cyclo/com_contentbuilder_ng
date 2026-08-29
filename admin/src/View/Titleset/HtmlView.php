<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\View\Titleset;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Model\TitlesetModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Document\HtmlDocument;
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
        $isDraft = ($this->data['filename'] ?? '') === ''
            || $app->getInput()->getBool('duplicate', false)
            || $app->getInput()->getBool('copy', false);
        $isProvided = ($this->data['source'] ?? '') === 'provided';
        ToolbarHelper::title(Text::_($isProvided
            ? 'COM_CONTENTBUILDERNG_TITLESETS_VIEW_TITLE'
            : 'COM_CONTENTBUILDERNG_TITLESETS_EDIT_TITLE'), $isProvided ? 'eye' : 'edit');
        if ($isProvided) {
            /** @var HtmlDocument $document */
            $document = $this->getDocument();
            /** @var Toolbar $toolbar */
            $toolbar = $document->getToolbar('toolbar');
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
        ToolbarHelper::saveGroup(
            [
                ['apply', 'titleset.apply', 'JTOOLBAR_APPLY'],
                ['save', 'titleset.save', 'JTOOLBAR_SAVE'],
                ['save2copy', 'titleset.save2copy', 'JTOOLBAR_SAVE_AS_COPY'],
            ],
            'btn-success'
        );
        ToolbarHelper::custom(
            'titleset.validateFile',
            'check',
            'check',
            'COM_CONTENTBUILDERNG_TITLESETS_VALIDATE',
            false
        );
        if (!$isDraft || $app->getInput()->getBool('saved', false)) {
            ToolbarHelper::custom(
                'titleset.cancel',
                'cancel',
                'cancel',
                'JTOOLBAR_CLOSE',
                false
            );
        } else {
            ToolbarHelper::cancel('titleset.cancel');
        }

        parent::display($tpl);
    }
}
