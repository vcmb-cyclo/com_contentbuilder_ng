<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.'/administrator/' .'components/' .'com_contentbuilder/' .'classes/' .'joomla_compat.php');

require_once(JPATH_SITE .'/administrator/components/com_contentbuilder_ng/classes/controllerlegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR .'/classes/contentbuilder.php');

class ContentbuilderControllerAjax extends CBController
{
    function __construct()
    {
        parent::__construct();
        
        contentbuilder::setPermissions(CBRequest::getInt('id',0),0, class_exists('cbFeMarker') ? '_fe' : '');
    }

    function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl',null));
        CBRequest::setVar('layout', CBRequest::getWord('layout',null));
        CBRequest::setVar('view', 'ajax');
        CBRequest::setVar('format', 'raw');
        
        parent::display();
    }
}
