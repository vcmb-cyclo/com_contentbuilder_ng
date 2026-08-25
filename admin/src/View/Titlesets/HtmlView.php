<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\View\Titlesets;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
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
        ToolbarHelper::link(
            Route::_('index.php?option=com_contentbuilderng&view=titleset', false),
            'JTOOLBAR_NEW',
            'new'
        );
        ToolbarHelper::link(
            Route::_('index.php?option=com_contentbuilderng&view=about', false),
            'JTOOLBAR_CLOSE',
            'cancel'
        );

        parent::display($tpl);
    }
}
