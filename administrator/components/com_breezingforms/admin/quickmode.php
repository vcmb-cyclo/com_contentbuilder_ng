<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

require_once ($ff_admpath . '/admin/quickmode.html.php');
require_once ($ff_admpath . '/admin/quickmode.class.php');
require_once ($ff_admpath . '/libraries/Zend/Json/Decoder.php');
require_once ($ff_admpath . '/libraries/Zend/Json/Encoder.php');
$iconBase = '../administrator/components/com_breezingforms/libraries/jquery/themes/quickmode/i/';

$quickMode = new QuickMode();

$form = BFRequest::getInt('form', 0);

switch ($task) {

	case 'doAjaxSave':

		$chunksLength = BFRequest::getInt('chunksLength', 0);
		$form = BFRequest::getInt('form', 0);
		$chunkIdx = BFRequest::getInt('chunkIdx', 0);
		$rndAdd = BFRequest::getVar('rndAdd', 0);
		$_dest = JPATH_SITE . '/media/breezingforms/ajax_cache/ajaxsave_' . $chunkIdx . '_' . $rndAdd . '.txt';
		$_chunk = BFRequest::getVar('chunk', '');
		@File::write($_dest, $_chunk);
		@ob_end_clean();

		if ($chunkIdx == $chunksLength - 1) {

			$contents = '';
			for ($i = 0; $i < $chunksLength; $i++) {
				$contents .= @BFFile::read(JPATH_SITE . '/media/breezingforms/ajax_cache/ajaxsave_' . $i . '_' . $rndAdd . '.txt');
				@File::delete(JPATH_SITE . '/media/breezingforms/ajax_cache/ajaxsave_' . $i . '_' . $rndAdd . '.txt');
			}

			$formId = 0;
			@ob_end_clean();

			$formId = $quickMode->save(
				$form,
				Zend_Json::decode(bf_b64dec($contents))
			);

			ob_start();

			// CONTENTBUILDER
			if (file_exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php')) {
				require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
				$cbForm = contentbuilder::getForm('com_breezingforms', $formId);
				$db = Factory::getContainer()->get(DatabaseInterface::class);
				$db->setQuery("Select id From #__contentbuilder_forms Where `type` = 'com_breezingforms' And `reference_id` = " . intval($formId));
				$cbForms = $db->loadColumn();
				if (is_object($cbForm) && count($cbForms)) {
					require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'tables' . DS . 'elements.php');
					foreach ($cbForms as $dataId) {
						contentbuilder::synchElements($dataId, $cbForm);
						$elements_table = new TableElements($db);
						$elements_table->reorder('form_id=' . $dataId);
					}
				}
			}
			ob_end_clean();

			echo $formId;
			exit;
			// CONTENTBUILDER END
		}

		exit;
		break;

	case 'save':

		$formId = BFRequest::getInt('form', 0);

		$fOptions = $quickMode->getFormOptions($formId);

		if ($fOptions == null) {
			$formName = 'QuickForm' . mt_rand(0, mt_getrandmax());
			$formTitle = $formName;
			$formEmailntf = 1;
			$formEmailadr = '';
			$formDesc = '';
		} else {
			$formName = $fOptions->name;
			$formTitle = $fOptions->title;
			$formEmailntf = $fOptions->emailntf;
			$formEmailadr = $fOptions->emailadr;
			$formDesc = $fOptions->description;
		}

		echo QuickModeHtml::showApplication($formId, $formName, $formTitle, $formDesc, $formEmailntf, $formEmailadr, $quickMode->getTemplateCode($formId), $quickMode->getElementScripts(), $quickMode->getThemes(), $quickMode->getThemesBootstrap(), $quickMode->getThemesBootstrap4());
		break;

	default:

		$fOptions = $quickMode->getFormOptions($form);

		if ($fOptions == null) {
			$formName = 'QuickForm' . mt_rand(0, mt_getrandmax());
			$formTitle = $formName;
			$formEmailntf = 1;
			$formEmailadr = '';
			$formDesc = '';
		} else {
			$formName = $fOptions->name;
			$formTitle = $fOptions->title;
			$formEmailntf = $fOptions->emailntf;
			$formEmailadr = $fOptions->emailadr;
			$formDesc = $fOptions->description;
		}

		$root = "{
			      	attributes: {
			      		'class' : 'bfQuickModeRootClass',
			      		id : 'bfQuickModeRoot',
			      		mdata : JQuery.toJSON(
                                                    {
			      				type : 'root'
                                                    }
						)
			      	},
			      	properties:
			      		{
			      			type : 'root',
			      			title: '" . addslashes($formName) . "',
			      			name: '',
			      			rollover: true,
			      			rolloverColor : '#ffc',
			      			toggleFields : '',
			      			description: '',
			      			mailNotification : false,
			      			mailRecipient: '',
			      			submitInclude: true,
			      			submitLabel: 'submit',
			    			cancelInclude: false,
			      			cancelLabel: 'reset',
			      			pagingInclude: true,
			      			pagingNextLabel : 'next',
                                                pagingPrevLabel : 'back',
			   			theme : 'default',
                                                themebootstrap : '',
                                                themebootstrapbefore: '',
                                                themebootstrapLabelTop : false,
                                                themebootstrapThemeEngine : 'bootstrap',
                                                themebootstrapUseHeroUnit : false,
                                                themebootstrapUseWell : false,
                                                themebootstrapUseProgress : false,
                                                themeusebootstraplegacy: true,
			   			fadeIn : false,
			   			lastPageThankYou : false,
			   			submittedScriptCondidtion: 0,
			   			submittedScriptCode : '',
						useErrorAlerts : false,
                                                useDefaultErrors : true,
                                                useBalloonErrors: false,
                                                disableJQuery: false,
                                                joomlaHint: false,
                                                mobileEnabled: false,
                                                forceMobile: false,
                                                forceMobileUrl: 'index.php'
			   		}
			      	,
			      	state: 'open',
			      	data: { title: '" . addslashes($formName) . "', icon: '" . $iconBase . 'icon_form.png' . "'},
			      	children : []
			      	}";
		$o = $root;
		if ($form != 0) {
			$o = $quickMode->getTemplateCode(BFRequest::getInt('form', ''));
		}

		echo QuickModeHtml::showApplication($form, $formName, $formTitle, $formDesc, $formEmailntf, $formEmailadr, $o, $quickMode->getElementScripts(), $quickMode->getThemes(), $quickMode->getThemesBootstrap(), $quickMode->getThemesBootstrap4());
}