<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );



if( !defined( 'DS' ) ){
    define('DS', DIRECTORY_SEPARATOR);
}

if(!function_exists('cb_b64enc')){
    
    function cb_b64enc($str){
        $base = 'base';
        $sixty_four = '64_encode';
        return call_user_func($base.$sixty_four, $str);
    }

}

if(!function_exists('cb_b64dec')){
    function cb_b64dec($str){
        $base = 'base';
        $sixty_four = '64_decode';
        return call_user_func($base.$sixty_four, $str);
    }
}

if( ( CBRequest::getCmd('controller','') == 'elementoptions' || CBRequest::getCmd('controller','') == 'storages' || CBRequest::getCmd('controller','') == 'forms' || CBRequest::getCmd('controller','') == 'users' ) && !Factory::getUser()->authorise('contentbuilder.manage', 'com_contentbuilder') ){

	Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

}

if ( !( CBRequest::getCmd('controller','') == 'elementoptions' || CBRequest::getCmd('controller','') == 'storages' || CBRequest::getCmd('controller','') == 'forms' || CBRequest::getCmd('controller','') == 'users' ) && !Factory::getUser()->authorise('contentbuilder.admin', 'com_contentbuilder'))
{
	Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
}

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');

$db     = Factory::getContainer()->get(DatabaseInterface::class);
$db->setQuery("Select `id`,`name` From #__contentbuilder_forms Where display_in In (1,2) And published = 1");
$forms = $db->loadAssocList();

/*
foreach($forms As $form){

    contentbuilder::createBackendMenuItem($form['id'], $form['name'], true);
}*/

// Require the base controller
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'controller.php' );

// Require specific controller if requested
$controller = trim(CBRequest::getWord('controller'));

if($controller) {
    $path = JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Create the controller
$classname    = 'ContentbuilderController'.ucfirst( $controller );
$controller   = new $classname( );

// Perform the Request task
$controller->execute( CBRequest::getWord( 'task' ) );

// Redirect if set by the controller
$controller->redirect();
