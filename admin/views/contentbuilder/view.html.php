<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');

class ContentbuilderViewContentbuilder extends HtmlView
{
    function display($tpl = null)
    {
        echo '
        <style type="text/css">
        .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
        </style>
        ';
        ToolBarHelper::title(Text::_('COM_CONTENTBUILDER_ABOUT') . '</span>', 'logo_left.png');
        parent::display($tpl);
    }
}
