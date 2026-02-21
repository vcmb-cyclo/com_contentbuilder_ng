<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\MVC\View\HtmlView;

require_once(JPATH_SITE.'/administrator/' .'components/' .'com_contentbuilder/' .'classes/' .'joomla_compat.php');
require_once(JPATH_SITE .'/administrator/components/com_contentbuilder_ng/classes/viewlegacy.php');

class ContentbuilderViewVerify extends HtmlView
{
    function display($tpl = null)
    {
        
        parent::display($tpl);
    }
}
