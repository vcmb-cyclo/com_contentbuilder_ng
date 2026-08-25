<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\View\Titlesets;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use CB\Component\Contentbuilderng\Administrator\Model\TitlesetsModel;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;

final class HtmlView extends BaseHtmlView
{
    protected array $items = [];

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        if (!$app instanceof AdministratorApplication) {
            throw new \RuntimeException('Unexpected application instance.');
        }
        if (!$app->getIdentity()->authorise('core.manage', 'com_contentbuilderng')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel();
        if (!$model instanceof TitlesetsModel) {
            throw new \RuntimeException('Unexpected titlesets model.');
        }
        $this->items = $model->getItems();

        ToolbarHelper::title(Text::_('COM_CONTENTBUILDERNG_TITLESETS_TITLE'), 'list');
        /** @var Toolbar $toolbar */
        $toolbar = $this->getDocument()->getToolbar('toolbar');
        $toolbar->linkButton('titleset-new')
            ->url(Route::_('index.php?option=com_contentbuilderng&view=titleset', false))
            ->text('JTOOLBAR_NEW')
            ->icon('icon-new')
            ->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_NEW_DESC')]);
        $toolbar->linkButton('titlesets-close')
            ->url(Route::_('index.php?option=com_contentbuilderng&view=about', false))
            ->text('JTOOLBAR_CLOSE')
            ->icon('icon-cancel')
            ->attributes(['title' => Text::_('COM_CONTENTBUILDERNG_TITLESETS_CLOSE_DESC')]);

        parent::display($tpl);
    }
}
