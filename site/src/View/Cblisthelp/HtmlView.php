<?php

/**
 * @package     ContentBuilderNG
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\View\Cblisthelp;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

final class HtmlView extends BaseHtmlView
{
    public string $pageTitle = '';
    public string $summary = '';

    /** @var list<array{title: string, content: string}> */
    public array $sections = [];

    #[\Override]
    public function display($tpl = null): void
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load(
            'plg_content_contentbuilderng_cblist',
            JPATH_PLUGINS . '/content/contentbuilderng_cblist',
            null,
            false,
            true
        );

        $this->pageTitle = Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_LABEL');
        $this->summary = Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_XML_DESCRIPTION');
        $this->sections = [
            [
                'title' => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_LABEL'),
                'content' => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_TEXT'),
            ],
            [
                'title' => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_FIELDS_LABEL'),
                'content' => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_FIELDS_TEXT'),
            ],
            [
                'title' => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_ACTIONS_LABEL'),
                'content' => Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_HELP_ACTIONS_TEXT'),
            ],
        ];

        $this->getDocument()->setTitle(strip_tags($this->pageTitle));

        parent::display($tpl);
    }
}
