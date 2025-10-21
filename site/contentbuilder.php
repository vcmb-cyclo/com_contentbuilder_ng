<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!function_exists('cb_b64enc')) {

	function cb_b64enc($str)
	{
		$base = 'base';
		$sixty_four = '64_encode';
		return call_user_func($base . $sixty_four, $str);
	}

}

if (!function_exists('cb_b64dec')) {
	function cb_b64dec($str)
	{
		$base = 'base';
		$sixty_four = '64_decode';
		return call_user_func($base . $sixty_four, $str);
	}
}

class cbFeMarker
{
}

// Require the base controller

require_once(JPATH_COMPONENT . DS . 'controller.php');

CBRequest::setVar('cb_controller', null);
CBRequest::setVar('cb_category_id', null);
CBRequest::setVar('cb_list_filterhidden', null);
CBRequest::setVar('cb_list_orderhidden', null);
CBRequest::setVar('cb_show_author', null);
CBRequest::setVar('cb_show_top_bar', null);
CBRequest::setVar('cb_show_details_top_bar', null);
CBRequest::setVar('cb_show_bottom_bar', null);
CBRequest::setVar('cb_show_details_bottom_bar', null);
CBRequest::setVar('cb_latest', null);
CBRequest::setVar('cb_show_details_back_button', null);
CBRequest::setVar('cb_list_limit', null);
CBRequest::setVar('cb_filter_in_title', null);
CBRequest::setVar('cb_prefix_in_title', null);
CBRequest::setVar('force_menu_item_id', null);
CBRequest::setVar('cb_category_menu_filter', null);

$menu = Factory::getApplication()->getMenu();
$item = $menu->getActive();

if (is_object($item)) {

	CBRequest::setVar('Itemid', $item->id);
}

if (CBRequest::getInt('Itemid', 0)) {

	$option = 'com_contentbuilder';

	if (CBRequest::getVar('layout', null) !== null) {
		Factory::getApplication()->getSession()->set('com_contentbuilder.layout.' . CBRequest::getInt('Itemid', 0) . CBRequest::getVar('layout', null), CBRequest::getVar('layout', null));
	}

	if (Factory::getApplication()->getSession()->get('com_contentbuilder.layout.' . CBRequest::getInt('Itemid', 0) . CBRequest::getVar('layout', null), null) !== null) {
		CBRequest::setVar('layout', Factory::getApplication()->getSession()->get('com_contentbuilder.layout.' . CBRequest::getInt('Itemid', 0) . CBRequest::getVar('layout', null), null));
	}

	if (is_object($item)) {

		if ($item->getParams()->get('form_id', null) !== null) {
			CBRequest::setVar('id', $item->getParams()->get('form_id', null));
		}
		if ($item->getParams()->get('record_id', null) !== null && $item->query['view'] == 'details' && !isset($_REQUEST['view'])) {
			CBRequest::setVar('record_id', $item->getParams()->get('record_id', null));
			CBRequest::setVar('controller', 'details');
		}
		if ($item->getParams()->get('record_id', null) !== null && $item->query['view'] == 'details' && isset($_REQUEST['view'])) {
			CBRequest::setVar('record_id', $item->getParams()->get('record_id', null));
			CBRequest::setVar('controller', 'edit');
		}
		if ($item->query['view'] == 'latest' && !isset($_REQUEST['view'])) {
			CBRequest::setVar('view', 'latest');
			CBRequest::setVar('controller', 'details');
		}
		if ($item->query['view'] == 'latest' && isset($_REQUEST['view']) && $_REQUEST['view'] == 'edit' && isset($_REQUEST['record_id'])) {
			CBRequest::setVar('record_id', $_REQUEST['record_id']);
			CBRequest::setVar('view', 'latest');
			CBRequest::setVar('controller', 'edit');
		}
		CBRequest::setVar('cb_category_id', $item->getParams()->get('cb_category_id', null));
		CBRequest::setVar('cb_controller', $item->getParams()->get('cb_controller', null));
		CBRequest::setVar('cb_list_filterhidden', $item->getParams()->get('cb_list_filterhidden', null));
		CBRequest::setVar('cb_list_orderhidden', $item->getParams()->get('cb_list_orderhidden', null));
		CBRequest::setVar('cb_show_author', $item->getParams()->get('cb_show_author', null));
		CBRequest::setVar('cb_show_bottom_bar', $item->getParams()->get('cb_show_bottom_bar', null));
		CBRequest::setVar('cb_show_top_bar', $item->getParams()->get('cb_show_top_bar', null));
		CBRequest::setVar('cb_show_details_bottom_bar', $item->getParams()->get('cb_show_details_bottom_bar', null));
		CBRequest::setVar('cb_show_details_top_bar', $item->getParams()->get('cb_show_details_top_bar', null));
		CBRequest::setVar('cb_show_details_back_button', $item->getParams()->get('cb_show_details_back_button', null));
		CBRequest::setVar('cb_list_limit', $item->getParams()->get('cb_list_limit', 20));
		CBRequest::setVar('cb_filter_in_title', $item->getParams()->get('cb_filter_in_title', 1));
		CBRequest::setVar('cb_prefix_in_title', $item->getParams()->get('cb_prefix_in_title', 1));
		CBRequest::setVar('force_menu_item_id', $item->getParams()->get('force_menu_item_id', 0));
		CBRequest::setVar('cb_category_menu_filter', $item->getParams()->get('cb_category_menu_filter', 0));
	}
}

// Require specific controller if requested
$controller = trim(CBRequest::getWord('controller'));

if (CBRequest::getCmd('view', '') == 'details' || (CBRequest::getCmd('view', '') == 'latest' && CBRequest::getCmd('controller', '') == '')) {
	$controller = 'details';
}

if (CBRequest::getVar('cb_controller') == 'edit') {
	$controller = 'edit';
} else if (CBRequest::getVar('cb_controller') == 'publicforms' && CBRequest::getInt('id', 0) <= 0) {
	$controller = 'publicforms';
}

if (!$controller) {

	$controller = 'list';

}

$path = JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php';
if (file_exists($path)) {
	require_once $path;
} else {
	$controller = '';
}

// Create the controller
$classname = 'ContentbuilderController' . ucfirst($controller);
$controller = new $classname();

// Perform the Request task
$controller->execute(CBRequest::getWord('task'));

// Redirect if set by the controller
$controller->redirect();
