<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA + GIL
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
            }'
        );

        // Et pour le title, garde un identifiant cohérent :
        ToolbarHelper::title('ContentBuilder :: ' . Text::_('COM_CONTENTBUILDER_NG_FORMS'), 'logo_left');
        ToolbarHelper::addNew('form.add');
        ToolbarHelper::custom('forms.copy', 'copy', '', Text::_('COM_CONTENTBUILDER_NG_COPY'));
        ToolbarHelper::editList('form.edit');

        ToolbarHelper::publish('forms.publish');
        ToolbarHelper::unpublish('forms.unpublish');
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'forms.delete');
        ToolbarHelper::preferences('com_contentbuilder_ng');

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
