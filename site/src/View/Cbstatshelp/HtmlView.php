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
        $app = Factory::getApplication();
        $language = $app->getLanguage();
        $languageTag = match ($app->getInput()->getString('help_lang')) {
            'fr', 'fr-FR' => 'fr-FR',
            'en', 'en-GB' => 'en-GB',
            'de', 'de-DE' => 'de-DE',
            default => $language->getTag(),
        };

        $language->load(
            'plg_content_contentbuilderng_cbstats',
            JPATH_PLUGINS . '/content/contentbuilderng_cbstats',
            $languageTag,
            true,
            true
        );

        $this->pageTitle = Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_LABEL');
        $this->summary = Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_XML_DESCRIPTION');
        $this->sections = [
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DISTINCT_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DISTINCT_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_ADD_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_FILTERS_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_PRESENTATION_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_PRESENTATION_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HEADERS_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HEADERS_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DISPLAY_OPTIONS_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DISPLAY_OPTIONS_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_CARD_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_CARD_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_CARD_GRID_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_CARD_GRID_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_CARD_WIDTH_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_CARD_WIDTH_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_ARTICLE_EXAMPLE_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_ARTICLE_EXAMPLE_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_CARD_CUSTOMIZE_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_CARD_CUSTOMIZE_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DIMENSIONS_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DIMENSIONS_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HIDE_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_HIDE_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_MANUAL_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_MANUAL_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_EXPORT_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_EXPORT_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DEBUG_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_DEBUG_TEXT'),
            $this->section('PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_REFERENCE_LABEL', 'PLG_CONTENT_CONTENTBUILDERNG_CBSTATS_HELP_REFERENCE_TEXT'),
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
