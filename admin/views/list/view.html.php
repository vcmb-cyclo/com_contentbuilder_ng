<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2024 by XDA+GIL
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// no direct access

defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\View\HtmlView;

require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');

class ContentbuilderViewList extends HtmlView
{
    function display($tpl = null)
    {
        // Get data from the model
        $subject = $this->get('Data');

        if (!class_exists('cbFeMarker')) {
            echo '
            <style type="text/css">
            .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
            </style>
            ';
            ToolBarHelper::title($subject->page_title . '</span>', 'logo_left.png');
        }


        $pagination = $this->get('Pagination');
        $total = $this->get('Total');

        $state = $this->get('state');
        $lists['order_Dir'] = $state->get('formsd_filter_order_Dir');
        $lists['order'] = $state->get('formsd_filter_order');
        $lists['filter'] = $state->get('formsd_filter');
        $lists['filter_state'] = $state->get('formsd_filter_state');
        $lists['filter_publish'] = $state->get('formsd_filter_publish');
        $lists['filter_language'] = $state->get('formsd_filter_language');
        $lists['limitstart'] = $state->get('limitstart');

        PluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);

        $dispatcher = Factory::getApplication()->getDispatcher();
        $eventResult = $dispatcher->dispatch('onListViewCss', new Joomla\Event\Event('onListViewCss', array()));
        $results = $eventResult->getArgument('result') ?: [];

        $theme_css = implode('', $results);
        $this->theme_css = $theme_css;

        PluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);
        $eventResult = $dispatcher->dispatch('onListViewJavascript', new Joomla\Event\Event('onListViewJavascript', array()));
        $results = $eventResult->getArgument('result') ?: [];

        $theme_js = implode('', $results);
        $this->theme_js = $theme_js;

        $this->show_filter = $subject->show_filter;
        $this->show_records_per_page = $subject->show_records_per_page;

        $this->page_class = $subject->page_class;
        $this->show_page_heading = $subject->show_page_heading;
        $this->slug = $subject->slug;
        $this->slug2 = $subject->slug2;
        $this->form_id = $subject->form_id;
        $this->labels = $subject->labels;
        $this->visible_cols = $subject->visible_cols;
        $this->linkable_elements = $subject->linkable_elements;
        $this->show_id_column = $subject->show_id_column;
        $this->page_title = $subject->page_title;
        $this->intro_text = $subject->intro_text;
        $this->export_xls = $subject->export_xls;
        $this->display_filter = $subject->display_filter;
        $this->edit_button = $subject->edit_button;
        $this->select_column = $subject->select_column;
        $this->states = $subject->states;
        $this->list_state = $subject->list_state;
        $this->list_publish = $subject->list_publish;
        $this->list_language = $subject->list_language;
        $this->list_article = $subject->list_article;
        $this->list_author = $subject->list_author;
        $this->list_rating = $subject->list_rating;
        $this->rating_slots = $subject->rating_slots;
        $this->state_colors = $subject->state_colors;
        $this->state_titles = $subject->state_titles;
        $this->published_items = $subject->published_items;
        $this->languages = $subject->languages;
        $this->lang_codes = $subject->lang_codes;
        $this->title_field = $subject->title_field;
        $this->lists = $lists;
        $this->items = $subject->items;
        $this->pagination = $pagination;
        $this->total = $total;
        $own_only = Factory::getApplication()->isClient('site') ? $subject->own_only_fe : $subject->own_only;
        $this->own_only = $own_only;
        parent::display($tpl);
    }
}
