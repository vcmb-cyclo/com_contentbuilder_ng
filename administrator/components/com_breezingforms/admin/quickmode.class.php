<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright   (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/
defined('_JEXEC') or die ('Direct Access to this location is not allowed.');

use Joomla\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

class QuickMode
{

	/**
	 * @$element['JDatabase
	 */
	private $db = null;

	function __construct()
	{
		$this->db = Factory::getContainer()->get(DatabaseInterface::class);

	}

	public function save($form, array $dataObject)
	{
		$areas = new stdClass();
		$areas->container = array();
		$areas->container[0] = array();
		$areas->container[0]['elements'] = array();
		$areas->container[0]['elementCount'] = 0;
		$this->createAreasFromTree($dataObject, $areas);

		// faking an item to load ff_validate_submit
		// needed by the built in submit button and paging
		$this->db->setQuery(
			"select id, name, code from #__facileforms_scripts " .
			"where published=1 And name = 'ff_validate_submit' " .
			"order by type, title, name, id desc"
		);
		$rows = $this->db->loadObjectList();
		$cnt = count($rows);
		if ($cnt != 0) {
			$element = $this->getDefaultElement();
			$element['title'] = 'bfFakeTitle';
			$element['name'] = 'bfFakeName';
			$element['logging'] = 0;
			$element['script2cond'] = 1;
			$element['script2id'] = $rows[0]->id;
			$areas->container[0]['elements'][] = $element;
		}
		$this->db->setQuery(
			"select id, name, code from #__facileforms_scripts " .
			"where published=1 And name = 'ff_resetForm' " .
			"order by type, title, name, id desc"
		);
		$rows = $this->db->loadObjectList();
		$cnt = count($rows);
		if ($cnt != 0) {
			$element = $this->getDefaultElement();
			$element['title'] = 'bfFakeTitle2';
			$element['name'] = 'bfFakeName2';
			$element['logging'] = 0;
			$element['script2cond'] = 1;
			$element['script2id'] = $rows[0]->id;
			$areas->container[0]['elements'][] = $element;
		}
		$this->db->setQuery(
			"select id, name, code from #__facileforms_scripts " .
			"where published=1 And name = 'ff_validate_prevpage' " .
			"order by type, title, name, id desc"
		);
		$rows = $this->db->loadObjectList();
		$cnt = count($rows);
		if ($cnt != 0) {
			$element = $this->getDefaultElement();
			$element['title'] = 'bfFakeTitle3';
			$element['name'] = 'bfFakeName3';
			$element['logging'] = 0;
			$element['script2cond'] = 1;
			$element['script2id'] = $rows[0]->id;
			$areas->container[0]['elements'][] = $element;
		}
		$this->db->setQuery(
			"select id, name, code from #__facileforms_scripts " .
			"where published=1 And name = 'ff_validate_nextpage' " .
			"order by type, title, name, id desc"
		);
		$rows = $this->db->loadObjectList();
		$cnt = count($rows);
		if ($cnt != 0) {
			$element = $this->getDefaultElement();
			$element['title'] = 'bfFakeTitle4';
			$element['name'] = 'bfFakeName4';
			$element['logging'] = 0;
			$element['script2cond'] = 1;
			$element['script2id'] = $rows[0]->id;
			$areas->container[0]['elements'][] = $element;
		}
		$mdata = $dataObject['properties'];

		return $this->save2($form, $mdata['name'], $mdata['title'], $mdata['description'], bf_b64enc(Zend_Json::encode($dataObject)), $areas->container, count($dataObject['children']));
	}

	public function getDefaultElement()
	{

		$element = array();
		$element['element'] = null;
		$element['bfType'] = '';
		$element['elementType'] = '';
		$element['options'] = array();
		$element['data1'] = '';
		$element['data2'] = '';
		$element['data3'] = '';
		$element['script1cond'] = 0;
		$element['script1id'] = 0;
		$element['script1code'] = '';
		$element['script1flag1'] = 0;
		$element['script1flag2'] = 0;
		$element['script2cond'] = 0;
		$element['script2id'] = 0;
		$element['script2code'] = '';
		$element['script2flag1'] = 0;
		$element['script2flag2'] = 0;
		$element['script2flag3'] = 0;
		$element['script2flag4'] = 0;
		$element['script2flag5'] = 0;
		$element['script3cond'] = 0;
		$element['script3id'] = 0;
		$element['script3code'] = '';
		$element['script3msg'] = '';
		$element['functionNameScript1'] = '';
		$element['functionNameScript2'] = '';
		$element['functionNameScript3'] = '';
		$element['flag1'] = 0;
		$element['flag2'] = 0;
		$element['mailback'] = 0;
		$element['mailbackfile'] = '';
		$element['title'] = '';
		$element['name'] = '';
		$element['page'] = 1;
		$element['orderNumber'] = 0;
		$element['dbId'] = 0;
		$element['appElementOrderId'] = 0;
		$element['id'] = 0;
		$element['logging'] = 1;
		$element['qId'] = 0;
		$element['internalType'] = '';

		return $element;

	}

	public function createAreasFromTree(array $dataObject, stdClass $areas, $page = 1)
	{

		$element = $this->getDefaultElement();

		if (isset ($dataObject['attributes']) && isset ($dataObject['properties'])) {
			$mdata = $dataObject['properties'];

			if ($mdata['type'] == 'root') {
				if (isset ($mdata['themebootstrap']) && $mdata['themebootstrap'] && isset ($mdata['themebootstrapvars']) && $mdata['themebootstrapvars'] && isset ($mdata['themebootstrapbefore']) && $mdata['themebootstrapbefore'] == $mdata['themebootstrap']) {
					$folder = 'themes-bootstrap';

					if (isset ($mdata['themebootstrapUse3']) && $mdata['themebootstrapUse3']) {

						$folder = 'themes-bootstrap3';
					}

					$themepath = JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . $folder . DS . $mdata['themebootstrap'] . DS;
					$themevarspath = $themepath . 'vars.txt';
					if (file_exists($themevarspath)) {
						File::write($themevarspath, $mdata['themebootstrapvars']);
					}
				}
			} else if ($mdata['type'] == 'page') {
				$ex = explode('bfQuickModePage', $dataObject['attributes']['id']);
				$page = $ex[1];
			} else if ($mdata['type'] == 'element') {

				$element['internalType'] = $mdata['bfType'];

				switch ($mdata['bfType']) {
					case 'bfTextfield':
						$element['bfType'] = 'Text';
						$element['options']['value'] = $mdata['value'];
						$element['options']['placeholder'] = isset ($mdata['placeholder']) ? $mdata['placeholder'] : '';
						$element['data1'] = $mdata['value'];
						$element['options']['password'] = $mdata['password'];
						$element['flag1'] = $mdata['password'] ? 1 : 0;
						$element['options']['mailback'] = $mdata['mailback'];
						$element['mailback'] = $mdata['mailback'] ? 1 : 0;
						$element['mailbackAsSender'] = $mdata['mailbackAsSender'] ? 1 : 0;
						$element['mailbackfile'] = $mdata['mailbackfile'];
						break;
					case 'bfTextarea':
						$element['bfType'] = 'Textarea';
						$element['options']['value'] = $mdata['value'];
						$element['data1'] = $mdata['value'];

						$element['options']['placeholder'] = isset ($mdata['placeholder']) ? $mdata['placeholder'] : '';
						break;
					case 'bfSelect':
						$element['bfType'] = 'Select List';
						$element['options']['multiple'] = $mdata['multiple'];
						$element['options']['options'] = $mdata['list'];
						$element['options']['mailback'] = $mdata['mailback'];
						$element['mailback'] = $mdata['mailback'] ? 1 : 0;
						$element['data1'] = 1;
						$element['data2'] = $mdata['list'];
						$element['flag1'] = $mdata['multiple'] ? 1 : 0;
						break;
					case 'bfRadioGroup':
						$element['bfType'] = 'Radio Group';
						$element['data2'] = $mdata['group'];
						break;
					case 'bfCheckboxGroup':
						$element['bfType'] = 'Checkbox Group';
						$element['data2'] = $mdata['group'];
						break;
					case 'bfSignature':
						$element['bfType'] = 'Signature';
						break;
					case 'bfCheckbox':
						$element['bfType'] = 'Checkbox';
						$element['options']['checked'] = $mdata['checked'];
						$element['flag1'] = $mdata['checked'] ? 1 : 0;
						$element['options']['value'] = $mdata['value'];
						$element['data1'] = $mdata['value'];
						$element['mailbackAccept'] = $mdata['mailbackAccept'];
						$element['mailbackAcceptConnectWith'] = $mdata['mailbackConnectWith'];
						break;
					case 'bfFile':
						$element['bfType'] = 'File Upload';
						$element['options']['allowedFileExtensions'] = strtolower($mdata['allowedFileExtensions']);
						$element['options']['timestamp'] = $mdata['timestamp'];
						$element['options']['useUrl'] = isset ($mdata['useUrl']) ? $mdata['useUrl'] : false;
						$element['options']['html5'] = isset ($mdata['html5']) ? $mdata['html5'] : false;
						$element['options']['useUrlDownloadDirectory'] = isset ($mdata['useUrlDownloadDirectory']) ? $mdata['useUrlDownloadDirectory'] : false;
						$element['options']['resize_target_width'] = isset ($mdata['resize_target_width']) ? $mdata['resize_target_width'] : '';
						$element['options']['resize_target_height'] = isset ($mdata['resize_target_height']) ? $mdata['resize_target_height'] : '';
						$element['options']['resize_type'] = isset ($mdata['resize_type']) ? $mdata['resize_type'] : '';
						$element['options']['resize_bgcolor'] = isset ($mdata['resize_bgcolor']) ? $mdata['resize_bgcolor'] : '';

						$element['flag1'] = $mdata['timestamp'] ? 1 : 0;
						$element['options']['uploadDirectory'] = $mdata['uploadDirectory'];
						$element['data1'] = $mdata['uploadDirectory'];
						$element['data2'] = strtolower($mdata['allowedFileExtensions']);
						$element['options']['attachToAdminMail'] = $mdata['attachToAdminMail'];
						$element['options']['attachToUserMail'] = $mdata['attachToUserMail'];
						break;
					case 'bfSubmitButton':
						$element['bfType'] = 'Regular Button';
						$element['options']['value'] = $mdata['value'];
						$element['options']['readonly'] = false;
						$element['data1'] = $mdata['value'];
						break;
					case 'bfHidden':
						$element['bfType'] = 'Hidden Input';
						$element['data1'] = $mdata['value'];
						break;
					case 'Summarize':
						$element['bfType'] = 'Summarize';
						break;
					case 'bfCaptcha':
						$element['bfType'] = 'Captcha';
						$element['width'] = isset ($mdata['width']) ? intval($mdata['width']) : 230;
						break;
					case 'bfNumberInput':
						$element['bfType'] = 'Number Input';
						$element['data1'] = $mdata['value'];
						break;
					case 'bfReCaptcha':
						$element['bfType'] = 'ReCaptcha';
						$element['pubkey'] = $mdata['pubkey'];
						$element['privkey'] = $mdata['privkey'];
						$element['theme'] = $mdata['theme'];
						break;
					case 'bfCalendar':
						$element['bfType'] = 'Calendar';
						$element['data1'] = $mdata['value'];
						break;
					case 'bfCalendarResponsive':
						$element['bfType'] = 'Calendar';
						$element['data1'] = $mdata['value'];
						break;
					case 'bfPayPal':
						$element['bfType'] = 'PayPal';
						$element['options']['testaccount'] = $mdata['testaccount'];
						$element['options']['useIpn'] = isset ($mdata['useIpn']) ? $mdata['useIpn'] : false;
						$element['options']['downloadableFile'] = $mdata['downloadableFile'];
						$element['options']['filepath'] = $mdata['filepath'];
						$element['options']['downloadTries'] = $mdata['downloadTries'];
						$element['options']['business'] = $mdata['business'];
						$element['options']['token'] = $mdata['token'];
						$element['options']['testBusiness'] = $mdata['testBusiness'];
						$element['options']['testToken'] = $mdata['testToken'];
						$element['options']['itemname'] = $mdata['itemname'];
						$element['options']['itemnumber'] = $mdata['itemnumber'];
						$element['options']['amount'] = $mdata['amount'];
						$element['options']['tax'] = $mdata['tax'];
						$element['options']['thankYouPage'] = $mdata['thankYouPage'];
						$element['options']['cancelURL'] = isset ($mdata['cancelURL']) ? $mdata['cancelURL'] : '';
						$element['options']['locale'] = $mdata['locale'];
						$element['options']['currencyCode'] = $mdata['currencyCode'];
						$element['options']['image'] = $mdata['image'];
						$element['options']['sendNotificationAfterPayment'] = $mdata['sendNotificationAfterPayment'];
						$element['data1'] = $mdata['image'];
						break;
					case 'bfStripe':
						$element['bfType'] = 'Stripe';
						$element['options']['downloadableFile'] = $mdata['downloadableFile'];
						$element['options']['filepath'] = $mdata['filepath'];
						$element['options']['downloadTries'] = $mdata['downloadTries'];
						$element['options']['secretKey'] = $mdata['secretKey'];
						$element['options']['publishableKey'] = $mdata['publishableKey'];
						$element['options']['itemname'] = $mdata['itemname'];
						$element['options']['amount'] = $mdata['amount'];
						$element['options']['thankYouPage'] = $mdata['thankYouPage'];
						$element['options']['currencyCode'] = $mdata['currencyCode'];
						$element['options']['sendNotificationAfterPayment'] = $mdata['sendNotificationAfterPayment'];
						$element['options']['image'] = $mdata['image'];
						$element['options']['emailfield'] = isset ($mdata['emailfield']) ? $mdata['emailfield'] : '';
						$element['data1'] = $mdata['image'];
						break;
					case 'bfSofortueberweisung':
						$element['bfType'] = 'Sofortueberweisung';
						$element['options']['mailback'] = $mdata['mailback'];
						$element['options']['downloadableFile'] = $mdata['downloadableFile'];
						$element['options']['filepath'] = $mdata['filepath'];
						$element['options']['downloadTries'] = $mdata['downloadTries'];
						$element['options']['user_id'] = $mdata['user_id'];
						$element['options']['project_id'] = $mdata['project_id'];
						$element['options']['project_password'] = $mdata['project_password'];
						$element['options']['reason_1'] = $mdata['reason_1'];
						$element['options']['reason_2'] = $mdata['reason_2'];
						$element['options']['amount'] = $mdata['amount'];
						$element['options']['thankYouPage'] = $mdata['thankYouPage'];
						$element['options']['language_id'] = $mdata['language_id'];
						$element['options']['currency_id'] = $mdata['currency_id'];
						$element['options']['image'] = $mdata['image'];
						$element['options']['sendNotificationAfterPayment'] = isset ($mdata['sendNotificationAfterPayment']) ? $mdata['sendNotificationAfterPayment'] : false;
						$element['data1'] = $mdata['image'];
						break;
					default:
						$element['bfType'] = 'Unknown';
				}

				$areas->container[0]['elementCount']++;

				// general
				$element['title'] = $mdata['label'];
				$element['name'] = $mdata['bfName'];
				$element['orderNumber'] = $mdata['orderNumber'] != -1 ? $mdata['orderNumber'] : $areas->container[0]['elementCount'];
				$element['tabIndex'] = $mdata['tabIndex'];
				$element['logging'] = $mdata['logging'];
				$element['options']['readonly'] = isset ($mdata['readonly']) ? $mdata['readonly'] : false;
				$element['flag2'] = isset ($mdata['readonly']) && $mdata['readonly'] ? 1 : 0;
				// validation
				$element['script3id'] = $mdata['validationId'];
				$element['script3code'] = $mdata['validationCode'];

				$element['script3msg'] = $mdata['validationMessage'];

				$element['functionNameScript3'] = $mdata['validationFunctionName'];
				$element['script3cond'] = $mdata['validationCondition'];
				// init
				$element['script1id'] = $mdata['initId'];
				$element['script1code'] = $mdata['initCode'];
				$element['script1flag1'] = isset ($mdata['initFormEntry']) ? $mdata['initFormEntry'] : '';
				$element['script1flag2'] = isset ($mdata['initPageEntry']) ? $mdata['initPageEntry'] : '';
				$element['functionNameScript1'] = $mdata['initFunctionName'];
				$element['script1cond'] = $mdata['initCondition'];
				// action
				$element['script2id'] = $mdata['actionId'];
				$element['script2code'] = isset ($mdata['actionCode']) ? $mdata['actionCode'] : '';
				$element['script2flag1'] = isset ($mdata['actionClick']) ? $mdata['actionClick'] : '';
				$element['script2flag2'] = isset ($mdata['actionBlur']) ? $mdata['actionBlur'] : '';
				$element['script2flag3'] = isset ($mdata['actionChange']) ? $mdata['actionChange'] : '';
				$element['script2flag4'] = isset ($mdata['actionFocus']) ? $mdata['actionFocus'] : '';
				$element['script2flag5'] = isset ($mdata['actionSelect']) ? $mdata['actionSelect'] : '';
				$element['functionNameScript2'] = $mdata['actionFunctionName'];
				$element['script2cond'] = isset ($mdata['actionCondition']) ? $mdata['actionCondition'] : '';
				$element['hideInMailback'] = isset ($mdata['hideInMailback']) ? $mdata['hideInMailback'] : false;
				$element['page'] = $page;
				$element['dbId'] = $mdata['dbId'];
				$element['qId'] = $dataObject['attributes']['id'];

				$areas->container[0]['elements'][] = $element;
			}
		}

		if (isset ($dataObject['children']) && count($dataObject['children']) != 0) {
			$childrenAmount = count($dataObject['children']);
			for ($i = 0; $i < $childrenAmount; $i++) {
				$this->createAreasFromTree($dataObject['children'][$i], $areas, $page);
			}
		}
	}

	public function updateDbId(&$dataObject, $id, $dbId)
	{

		if (isset ($dataObject['attributes']) && isset ($dataObject['properties'])) {
			$mdata = $dataObject['properties'];

			if ($mdata['type'] == 'element' && $dataObject['attributes']['id'] === $id) {
				$mdata['dbId'] = $dbId;
				$dataObject['properties'] = $mdata;

				return;
			}
		}

		if (isset ($dataObject['children']) && count($dataObject['children']) != 0) {
			$childrenAmount = count($dataObject['children']);
			for ($i = 0; $i < $childrenAmount; $i++) {
				$this->updateDbId($dataObject['children'][$i], $id, $dbId);
			}
		}
	}

	public function save2($form, $formName, $formTitle, $formDescription, $templateCode, array $areas, $pages = 1)
	{

		$dataObject = Zend_Json::decode(bf_b64dec($templateCode));
		$mdata = $dataObject['properties'];

		$this->db->setQuery("Select id From #__facileforms_forms Where id = " . $this->db->Quote($form) . "");

		if (count($this->db->loadObjectList()) == 0) {

			$scriptCond1 = "";
			$scriptCond2 = "";
			if ($mdata['submittedScriptCondidtion'] != -1) {
				$scriptCond1 = ",
							script2cond,
							script2code";
				$scriptCond2 = ",
							" . $this->db->Quote($mdata['submittedScriptCondidtion']) . ",
							" . $this->db->Quote($mdata['submittedScriptCode']);
			}

			$this->db->setQuery(
				"Insert Into #__facileforms_forms
						(
                                                        package,
							template_code,
							template_areas,
							published,
							name,
							title,
							description,
							class1,
							width,
							height,
							pages,
							emailntf,
							emailadr
                            " . $scriptCond1 . "
						)
						Values
						(
                                                        'QuickModeForms',
							" . trim($this->db->Quote($templateCode), "\t, ,\n,\r") . ",
							" . $this->db->Quote(Zend_Json::encode($areas)) . ",
							'1',
							" . trim($this->db->Quote($formName), "\t, ,\n,\r") . ",
							" . trim($this->db->Quote($formTitle), "\t, ,\n,\r") . ",
							" . trim($this->db->Quote($formDescription), "\t, ,\n,\r") . ",
							'',
							'400',
							'500',
							" . $this->db->Quote($pages) . ",
							" . $this->db->Quote($mdata['mailNotification'] ? 2 : 0) . ",
							" . $this->db->Quote($mdata['mailRecipient']) . "
                                                        " . $scriptCond2 . "

						)"
			);

			$this->db->execute();
			$form = $this->db->insertid();

		} else {

			// preventing mysql has gone away errors by splitting the template code string into chunks
			// and sending each chunk in a seperate query
			$length = strlen(trim($templateCode));
			$chunks = array();
			$chunk = '';
			$cnt = 0;
			for ($i = 0; $i < $length; $i++) {
				$chunk .= $templateCode[$i];
				$cnt++;
				if ($cnt == 60000 || ($i + 1 == $length && $cnt + 1 < 60000)) {
					$chunks[] = $chunk;
					$chunk = '';
					$cnt = 0;
				}
			}


			$scriptCond = "";
			if ($mdata['submittedScriptCondidtion'] != -1) {
				$scriptCond = ",
							script2cond = " . $this->db->Quote($mdata['submittedScriptCondidtion']) . ",
							script2code = " . $this->db->Quote($mdata['submittedScriptCode']);
			}


			$emailntf = 0;

			if ($mdata['mailNotification'] && trim($mdata['mailRecipient']) == '') {

				$emailntf = 1;
			}

			if ($mdata['mailNotification'] && trim($mdata['mailRecipient']) != '') {

				$emailntf = 2;
			}

			$this->db->setQuery(
				"Update
							#__facileforms_forms
						 Set
							template_code = '' ,
							template_areas = " . $this->db->Quote(Zend_Json::encode($areas)) . ",
							name = " . trim($this->db->Quote($formName), "\t, ,\n,\r") . ",
							title = " . trim($this->db->Quote($formTitle), "\t, ,\n,\r") . ",
							description = " . trim($this->db->Quote($formDescription), "\t, ,\n,\r") . ",
							pages = " . $this->db->Quote($pages) . ",
							emailntf = " . $this->db->Quote($emailntf) . ",
							emailadr = " . $this->db->Quote($mdata['mailRecipient']) . "
                                                        " . $scriptCond . "
						 Where
							id = " . $this->db->Quote($form) . "
						"
			);

			$this->db->execute();

			$chunkLength = count($chunks);
			for ($i = 0; $i < $chunkLength; $i++) {
				$this->db->setQuery(
					"Update
							#__facileforms_forms
						 Set
							template_code = Concat(template_code," . $this->db->Quote($chunks[$i], "\t, ,\n,\r") . ")
						 Where
							id = " . $this->db->Quote($form) . "
						"
				);

				$this->db->execute();
			}
		}

		$notRemoveIds = '';

		$i = 0;

		$elementCount = 0;



		foreach ($areas[0]['elements'] as $element) {

			$elementId = -1;

			if ($element['dbId'] == 0) {

				$this->db->setQuery(
					"Insert Into #__facileforms_elements
							(
								mailback,
								mailbackfile,
								form,
								page,
								published,
								ordering,
								name,
								title,
								type,
								class1,
								class2,
								logging,
								posx,
								posxmode,
								posy,
								posymode,
								width,
								widthmode,
								height,
								heightmode,
								flag1,
								flag2,
								data1,
								data2,
								data3,
								script1cond,
								script1id,
								script1code,
								script1flag1,
								script1flag2,
								script2cond,
								script2id,
								script2code,
								script2flag1,
								script2flag2,
								script2flag3,
								script2flag4,
								script2flag5,
								script3cond,
								script3id,
								script3code,
								script3msg
							)
							Values
							(
								" . $this->db->Quote($element['mailback']) . ",
								" . $this->db->Quote($element['mailbackfile']) . ",
								" . $this->db->Quote($form) . ",
								" . $this->db->Quote(isset ($element['page']) ? $element['page'] : 1) . ",
								'1',
								" . $this->db->Quote($element['orderNumber']) . ",
								" . $this->db->Quote($element['name']) . ",
								" . $this->db->Quote($element['title']) . ",
								" . $this->db->Quote($element['bfType']) . ",
								'',
								'',
								" . $this->db->Quote($element['logging'] ? 1 : 0) . ",
								'0',
								'0',
								'" . (40 * $elementCount) . "',
								'0',
								'20',
								'0',
								'20',
								'0',
								" . $this->db->Quote($element['flag1']) . ",
								" . $this->db->Quote($element['flag2']) . ",
								" . $this->db->Quote($element['data1']) . ",
								" . $this->db->Quote($element['data2']) . ",
								" . $this->db->Quote($element['data3']) . ",
								" . $this->db->Quote($element['script1cond']) . ",
								" . $this->db->Quote($element['script1id']) . ",
								" . $this->db->Quote($element['script1code']) . ",
								" . $this->db->Quote($element['script1flag1']) . ",
								" . $this->db->Quote($element['script1flag2']) . ",
								" . $this->db->Quote($element['script2cond']) . ",
								" . $this->db->Quote($element['script2id']) . ",
								" . $this->db->Quote($element['script2code']) . ",
								" . $this->db->Quote($element['script2flag1']) . ",
								" . $this->db->Quote($element['script2flag2']) . ",
								" . $this->db->Quote($element['script2flag3']) . ",
								" . $this->db->Quote($element['script2flag4']) . ",
								" . $this->db->Quote($element['script2flag5']) . ",
								" . $this->db->Quote($element['script3cond']) . ",
								" . $this->db->Quote($element['script3id']) . ",
								" . $this->db->Quote($element['script3code']) . ",
								" . $this->db->Quote($element['script3msg']) . "
							)"
				);


				$bError = false;
				try {
					$this->db->execute();
				} catch (\InvalidArgumentException $e) {
					$bError = true;
					//$encoded = $element['bfType']  . ' - ' . $element['bfType'] . ' - ' . $this->db->getErrormsg() . "\n";
					//file_put_contents(JPATH_SITE.'/error.txt', $encoded, FILE_APPEND);
				}

				if ($bError == false) {
					$elementId = $this->db->insertid();
					$areas[0]['elements'][$elementCount]['dbId'] = $elementId;
					$this->updateDbId($dataObject, $areas[0]['elements'][$elementCount]['qId'], $elementId);
				}
			} else {

				// fix ids of copied elements

				$this->db->setQuery("Select id From #__facileforms_elements Where name = " . $this->db->Quote($element['name']) . " And form = " . $this->db->Quote($form) . " ");

				$bError = false;
				try {
					$elementCheck = $this->db->loadObjectList();
				} catch (\InvalidArgumentException $e) {
					$bError = true;
				}

				if ($bError == false) {
					foreach ($elementCheck as $check) {
						if ($check->id != intval($element['dbId'])) {
							$element['dbId'] = $check->id;
							$areas[0]['elements'][$elementCount]['dbId'] = $check->id;
							$this->updateDbId($dataObject, $areas[0]['elements'][$elementCount]['qId'], $check->id);
						}
					}
				}

				$this->db->setQuery(
					"Update #__facileforms_elements Set
								mailback=" . $this->db->Quote($element['mailback']) . ",
								mailbackfile=" . $this->db->Quote($element['mailbackfile']) . ",
								form=" . $this->db->Quote($form) . ",
								page=" . $this->db->Quote(isset ($element['page']) ? $element['page'] : 1) . ",
								published='1',
								ordering=" . $this->db->Quote($element['orderNumber']) . ",
								name=" . $this->db->Quote($element['name']) . ",
								title=" . $this->db->Quote($element['title']) . ",
								type=" . $this->db->Quote($element['bfType']) . ",
								class1='',
								class2='',
								logging=" . $this->db->Quote($element['logging'] ? 1 : 0) . ",
								posx='0',
								posxmode='0',
								posy='" . (40 * $elementCount) . "',
								posymode='0',
								width='20',
								widthmode='0',
								height='20',
								heightmode='0',
								flag1=" . $this->db->Quote($element['flag1']) . ",
								flag2=" . $this->db->Quote($element['flag2']) . ",
								data1=" . $this->db->Quote($element['data1']) . ",
								data2=" . $this->db->Quote($element['data2']) . ",
								data3=" . $this->db->Quote($element['data3']) . ",
								script1cond=" . $this->db->Quote($element['script1cond']) . ",
								script1id=" . $this->db->Quote($element['script1id']) . ",
								script1code=" . $this->db->Quote($element['script1code']) . ",
								script1flag1=" . $this->db->Quote($element['script1flag1']) . ",
								script1flag2=" . $this->db->Quote($element['script1flag2']) . ",
								script2cond=" . $this->db->Quote($element['script2cond']) . ",
								script2id=" . $this->db->Quote($element['script2id']) . ",
								script2code=" . $this->db->Quote($element['script2code']) . ",
								script2flag1=" . $this->db->Quote($element['script2flag1']) . ",
								script2flag2=" . $this->db->Quote($element['script2flag2']) . ",
								script2flag3=" . $this->db->Quote($element['script2flag3']) . ",
								script2flag4=" . $this->db->Quote($element['script2flag4']) . ",
								script2flag5=" . $this->db->Quote($element['script2flag5']) . ",
								script3cond=" . $this->db->Quote($element['script3cond']) . ",
								script3id=" . $this->db->Quote($element['script3id']) . ",
								script3code=" . $this->db->Quote($element['script3code']) . ",
								script3msg=" . $this->db->Quote($element['script3msg']) . "
							Where
								id = " . $this->db->Quote($element['dbId']) . "
							"
				);

				$bError = false;
				try {
					$this->db->execute();
				} catch (\InvalidArgumentException $e) {
					$bError = true;
					//$encoded = $element['bfType']  . ' - ' . $element['bfType'] . ' - ' . $this->db->getErrormsg() . "\n";
					//file_put_contents(JPATH_SITE.'/error.txt', $encoded, FILE_APPEND);
				}

				$elementId = $element['dbId'];
			}

			$notRemoveIds .= ' id<>' . $this->db->Quote($elementId) . ' And ';
			$elementCount++;
		}

		$i++;


		if (strlen($notRemoveIds) != 0) {
			$this->db->setQuery("Delete From #__facileforms_elements Where " . $notRemoveIds . " form = " . $this->db->Quote($form) . " ");
			$this->db->execute();
		} else {
			$this->db->setQuery("Delete From #__facileforms_elements Where form = " . $this->db->Quote($form) . " ");
			$this->db->execute();
		}

		// preventing mysql has gone away errors by splitting the template code string into chunks
		// and sending each chunk in a seperate query
		$templateCode = bf_b64enc(Zend_Json::encode($dataObject));
		$length = strlen($templateCode);
		$chunks = array();
		$chunk = '';
		$cnt = 0;
		for ($i = 0; $i < $length; $i++) {
			$chunk .= $templateCode[$i];
			$cnt++;
			if ($cnt == 60000 || ($i + 1 == $length && $cnt + 1 < 60000)) {
				$chunks[] = $chunk;
				$chunk = '';
				$cnt = 0;
			}
		}

		$this->db->setQuery(
			"Update
							#__facileforms_forms
						 Set
						 	template_code = '',
						 	template_code_processed = 'QuickMode',
							template_areas          = " . $this->db->Quote(Zend_Json::encode($areas)) . "
						 Where
							id = " . $this->db->Quote($form) . "
						"
		);
		$this->db->execute();

		$chunkLength = count($chunks);
		for ($i = 0; $i < $chunkLength; $i++) {
			$this->db->setQuery(
				"Update
                                                                #__facileforms_forms
                                                         Set
                                                                template_code = Concat(template_code, " . $this->db->Quote($chunks[$i]) . ")
                                                         Where
                                                                id = " . $this->db->Quote($form) . "
                                                        "
			);
			$this->db->execute();
		}

		return $form;
	}

	public function getElementScripts()
	{
		$retArray = array();
		$this->db->setQuery("Select id, package, name, title, description, type From #__facileforms_scripts Where published = 1 And type = 'Element Validation'");
		$retArray['validation'] = $this->db->loadObjectList();
		$this->db->setQuery("Select id, package, name, title, description, type From #__facileforms_scripts Where published = 1 And type = 'Element Action'");
		$retArray['action'] = $this->db->loadObjectList();
		$this->db->setQuery("Select id, package, name, title, description, type From #__facileforms_scripts Where published = 1 And type = 'Element Init'");
		$retArray['init'] = $this->db->loadObjectList();
		;

		return $retArray;
	}

	public function getTemplateCode($form)
	{
		$this->db->setQuery("Select template_code From #__facileforms_forms Where id = " . $this->db->Quote($form) . "");
		$objList = $this->db->loadObjectList();
		$objListCount = count($objList);

		if ($objListCount == 1) {
			return bf_b64dec($objList[0]->template_code);
		}

		return '';
	}

	public function getFormOptions($form)
	{
		$this->db->setQuery("Select package, name, title, description, emailntf, emailadr From #__facileforms_forms Where id = " . $this->db->Quote($form) . "");
		$objList = $this->db->loadObjectList();
		$objListCount = count($objList);
		if ($objListCount == 1) {
			return $objList[0];
		}

		return null;
	}

	public function getThemes()
	{
		$themes = array();
		$folder = JPATH_SITE . '/media/breezingforms/themes/';
		if ($handle = opendir($folder)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "images" && $file != "." && $file != ".." && strtolower($file) != '.csv' && strtolower($file) != '.svn' && strtolower($file) != 'img') {
					if (@is_dir($folder . $file)) {
						$themes[] = $file;
					}
				}
			}
			closedir($handle);
		}

		return $themes;
	}

	public function getThemesBootstrap()
	{
		$themes = array();
		$folder = JPATH_SITE . '/media/breezingforms/themes-bootstrap4/';
		if ($handle = opendir($folder)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "images" && $file != "." && $file != ".." && strtolower($file) != '.csv' && strtolower($file) != '.svn' && strtolower($file) != 'img') {
					if (@is_dir($folder . $file)) {
						$themes[] = $file;
					}
				}
			}
			closedir($handle);
		}

		return $themes;
	}

	public function getThemesBootstrap4()
	{
		$themes = array();
		$folder = JPATH_SITE . '/media/breezingforms/themes-bootstrap4/';
		if ($handle = opendir($folder)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "images" && $file != "." && $file != ".." && strtolower($file) != '.csv' && strtolower($file) != '.svn' && strtolower($file) != 'img') {
					if (@is_dir($folder . $file)) {
						$themes[] = $file;
					}
				}
			}
			closedir($handle);
		}

		return $themes;
	}
}