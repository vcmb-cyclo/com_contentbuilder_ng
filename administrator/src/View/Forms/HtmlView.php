<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA + GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\Forms;

// no direct access
\defined('_JEXEC') or die('Restricted access');

//use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use CB\Component\Contentbuilder\Administrator\View\Contentbuilder\CBHtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    function display($tpl = null)
    {
        
        echo '
        <style type="text/css">
        .icon-48-logo_left { background-image: url(../media/com_contentbuilder/images/logo_left.png); }
        </style>
        ';

        ToolBarHelper::title('ContentBuilder :: ' . Text::_('COM_CONTENTBUILDER_FORMS') . '</span>', 'logo_left.png');
        ToolBarHelper::addNew('form.add');
        ToolBarHelper::custom('forms.copy', 'copy', '', Text::_('COM_CONTENTBUILDER_COPY'));
        ToolBarHelper::editList('form.edit');

        ToolbarHelper::publish('forms.publish');
        ToolbarHelper::unpublish('forms.unpublish');
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'forms.delete');
        ToolBarHelper::preferences('com_contentbuilder');

        $items      = $this->getModel()->getItems();
        $pagination = $this->getModel()->getPagination();
        $state      = $this->getModel()->getState();

        $tags = $this->getModel()->getTags();

        $lists['order']      = (string) $state->get('list.ordering', 'a.ordering');
        $lists['order_Dir']  = (string) $state->get('list.direction', 'ASC');
        $lists['state']      = HTMLHelper::_('grid.state', (string) $state->get('filter.state', ''));
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
