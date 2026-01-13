<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA + GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\Users;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
class HtmlView extends BaseHtmlView
{
    /**
     * @var  array
     */
    protected $items;

    /**
     * @var  \JPagination
     */
    protected $pagination;

    /**
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    public function display($tpl = null): void
    {
        // Récupération standard ListModel (J4/5/6)
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // Toolbar
        ToolbarHelper::title(
            '<span style="display:inline-block; vertical-align:middle">' . Text::_('COM_CONTENTBUILDER_USERS') . '</span>',
            'users'
        );

        ToolbarHelper::editList();

        parent::display($tpl);
    }
}
