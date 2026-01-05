<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\PublicForms;

// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    function display($tpl = null)
    {
        // Get data from the model
        $items = $this->get('Data');
        $perms = $this->get('Permissions');
        $page_heading = $this->get('ShowPageHeading');
        $introtext = $this->get('ShowIntrotext');
        $show_tags = $this->get('ShowTags');
        $show_id = $this->get('ShowId');
        $show_permissions = $this->get('ShowPermissions');
        $show_permissions_new = $this->get('ShowPermissionsNew');
        $show_permissions_edit = $this->get('ShowPermissionsEdit');
        $pagination = $this->get('Pagination');
        $tags = $this->get('Tags');

        $state = $this->get('state');

        $lists['order_Dir'] = $state->get('forms_filter_order_Dir');
        $lists['order'] = $state->get('forms_filter_order');
        $lists['state'] = HTMLHelper::_('grid.state', $state->get('forms_filter_state'));
        $lists['limitstart'] = $state->get('limitstart');
        $lists['filter_tag'] = $state->get('forms_filter_tag');

        $ordering = ($lists['order'] == 'ordering');

        $this->show_permissions = $show_permissions;
        $this->show_permissions_new = $show_permissions_new;
        $this->show_permissions_edit = $show_permissions_edit;
        $this->page_heading = $page_heading;
        $this->show_tags = $show_tags;
        $this->show_id = $show_id;
        $this->introtext = $introtext;
        $this->perms = $perms;
        $this->ordering = $ordering;
        $this->tags = $tags;
        $this->lists = $lists;

        $this->items = $items;
        $this->pagination = $pagination;
        parent::display($tpl);
    }
}
