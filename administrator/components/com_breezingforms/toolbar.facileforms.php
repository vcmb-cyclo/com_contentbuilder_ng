<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.9
* @package BreezingForms
* @copyright (C) 2008-2020 by Markus Bopp
* @copyright   Copyright (C) 2024 by XDA+GIL
* @license Released under the terms of the GNU General Public License
**/
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

use Joomla\CMS\Factory;


$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

global $ff_mospath, $ff_admpath, $ff_compath;
global $ff_mossite, $ff_admsite, $ff_admicon, $ff_comsite;
global $ff_config, $ff_compatible, $ff_install;

$mainframe = Factory::getApplication();

// load ff stuff and get config
require_once($ff_compath.'/facileforms.class.php');
require_once($ff_admpath.'/admin/config.class.php');
$ff_config = new facileFormsConfig();
initFacileForms();
$ff_admsite = $ff_mossite.'/administrator'.$comppath;
$ff_admicon = $ff_admsite.'/images/icons';

// load html file
require_once($ff_admpath.'/toolbar.facileforms.html.php');

$ff_compatible = true;
if ($ff_compatible) {
	// check for post installation tasks
	if ($act != 'installation')
		$ff_install = !file_exists(JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS .'facileforms.config.php');

	if (!$ff_install)
		switch ($act) {
			case 'configuration':
				if ($task == 'instpackage')
					menuFacileForms::INSTPACKAGE_MENU();
				break;
		} // switch
} // if
?>