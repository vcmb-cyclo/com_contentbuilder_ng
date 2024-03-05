<?php
/**
 * @package     BreezingForms
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$controller = JControllerLegacy::getInstance('Breezingforms');
$controller->execute('');
$controller->redirect();

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_breezingforms' . DS . 'admin.breezingforms.php');

Factory::getDocument()->addScript(JUri::root(true) . '/administrator/components/com_breezingforms/assets/js/custom.js');
Factory::getDocument()->addStyleSheet(JUri::root(true) . '/administrator/components/com_breezingforms/assets/css/custom.css');

Factory::getDocument()->addStyleSheet(JUri::root(true) . '/administrator/components/com_breezingforms/assets/font-awesome/css/font-awesome.css');


$recs = BFRequest::getVar('act', '') == 'managerecs' || BFRequest::getVar('act', '') == 'recordmanagement' || BFRequest::getVar('act', '') == '';
$mgforms = BFRequest::getVar('act', '') == 'manageforms' || BFRequest::getVar('act', '') == 'easymode' || BFRequest::getVar('act', '') == 'quickmode';
$mgscripts = BFRequest::getVar('act', '') == 'managescripts';
$mgpieces = BFRequest::getVar('act', '') == 'managepieces';
$mgintegrate = BFRequest::getVar('act', '') == 'integrate';
$mgmenus = BFRequest::getVar('act', '') == 'managemenus';
$mgconfig = BFRequest::getVar('act', '') == 'configuration';

$add = '';
if ($recs)
    $add = ': ' . Text::_('COM_BREEZINGFORMS_MANAGERECS');
if ($mgforms)
    $add = ': ' . Text::_('COM_BREEZINGFORMS_MANAGEFORMS');
if ($mgscripts)
    $add = ': ' . Text::_('COM_BREEZINGFORMS_MANAGESCRIPTS');
if ($mgpieces)
    $add = ': ' . Text::_('COM_BREEZINGFORMS_MANAGEPIECES');
if ($mgintegrate)
    $add = ': ' . Text::_('COM_BREEZINGFORMS_INTEGRATOR');
if ($mgmenus)
    $add = ': ' . Text::_('COM_BREEZINGFORMS_MANAGEMENUS');
if ($mgconfig)
    $add = ': ' . Text::_('COM_BREEZINGFORMS_CONFIG');

$app = Factory::getApplication();
$app->JComponentTitle = "BreezingForms" . $add;
$app->JComponentTitle = "<h1 class=\"page-title\">BreezingForms" . $add . '</h1>';