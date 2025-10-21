<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'controllerlegacy.php');

class ContentbuilderControllerPublicforms extends CBController
{
    function __construct()
    {
        parent::__construct();
    }

    function display($cachable = false, $urlparams = array())
    {
        CBRequest::setVar('tmpl', CBRequest::getWord('tmpl',null));
        CBRequest::setVar('layout', CBRequest::getWord('layout',null));
        CBRequest::setVar('view', 'publicforms');

        parent::display();
    }
}
