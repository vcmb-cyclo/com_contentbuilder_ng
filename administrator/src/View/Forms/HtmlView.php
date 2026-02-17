<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Administrator\View\Forms;

// No direct access
\defined('_JEXEC') or die('Restricted access');

//use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use CB\Component\Contentbuilder_ng\Administrator\View\Contentbuilder_ng\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    function display($tpl = null)
    {
        if ($this->getLayout() === 'help') {
            parent::display($tpl);
            return;
        }

        $wa = $this->document->getWebAssetManager();
        $wa->addInlineStyle(
            '.icon-logo_left{
                background-image:url(' . Uri::root(true) . '/media/com_contentbuilder_ng/images/logo_left.png);
                background-size:contain;
                background-repeat:no-repeat;
                background-position:center;
                display:inline-block;
                width:48px;
                height:48px;
                vertical-align:middle;
            }'
        );

        // Et pour le title, garde un identifiant cohérent :
        ToolbarHelper::title(Text::_('COM_CONTENTBUILDER_NG') .' :: ' . Text::_('COM_CONTENTBUILDER_NG_FORMS'), 'logo_left');
        ToolbarHelper::addNew('form.add');
        ToolbarHelper::custom('forms.copy', 'copy', '', Text::_('COM_CONTENTBUILDER_NG_COPY'));
        ToolbarHelper::editList('form.edit');

        ToolbarHelper::publish('forms.publish');
        ToolbarHelper::unpublish('forms.unpublish');
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'forms.delete');
        ToolbarHelper::preferences('com_contentbuilder_ng');
        ToolbarHelper::help(
            'COM_CONTENTBUILDER_NG_HELP_VIEWS_TITLE',
            false,
            Uri::base() . 'index.php?option=com_contentbuilder_ng&view=forms&layout=help&tmpl=component'
        );

        $items      = $this->getModel()->getItems();
        $pagination = $this->getModel()->getPagination();
        $state      = $this->getModel()->getState();

        $tags = $this->getModel()->getTags();

        $lists['order']      = (string) $state->get('list.ordering', 'a.ordering');
        $lists['order_Dir']  = (string) $state->get('list.direction', 'ASC');
        $lists['state']      = HTMLHelper::_('grid.state', (string) $state->get('filter.state', ''));
        $lists['filter_state'] = (string) $state->get('filter.state', '');
        $lists['filter_search'] = (string) $state->get('filter.search', '');
        $lists['filter_tag'] = (string) $state->get('filter.tag', '');

        $ordering = ($lists['order'] === 'a.ordering');

        $this->ordering = $ordering;
        $this->tags = $tags;
        $this->lists = $lists;
        $this->items = $items;
        $this->pagination = $pagination;

        parent::display($tpl);
    }
}
