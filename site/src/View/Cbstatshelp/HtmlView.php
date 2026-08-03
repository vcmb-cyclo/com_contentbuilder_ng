<?php

/**
 * @package     ContentBuilderNG
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\View\Cbstatshelp;

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
        Factory::getApplication()->getLanguage()->load(
            'plg_content_contentbuilderng_cbstats',
            JPATH_PLUGINS . '/content/contentbuilderng_cbstats',
            null,
            false,
            true
        );

        $this->pageTitle = Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_LABEL');
        $this->summary = Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_XML_DESCRIPTION');
        $this->sections = [
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_ADD_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_FILTERS_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_PRESENTATION_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_PRESENTATION_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HEADERS_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HEADERS_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DISPLAY_OPTIONS_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DISPLAY_OPTIONS_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HIDE_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HIDE_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_MANUAL_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_MANUAL_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_EXPORT_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_EXPORT_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DEBUG_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DEBUG_TEXT'),
        ];

        $this->getDocument()->setTitle(strip_tags($this->pageTitle));

        parent::display($tpl);
    }

    /** @return array{title: string, content: string} */
    private function section(string $titleKey, string $contentKey): array
    {
        return ['title' => Text::_($titleKey), 'content' => Text::_($contentKey)];
    }
}
