<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

class EasyModeHtml
{

	public static function showApplication($formId = 0, $formName = '', $templateCode = '', $callbackParams = array(), $elementScripts = array(), $pages = 1, $page = 1)
	{
		HTMLHelper::_('behavior.keepalive');
		?>

		<style type="text/css">
			<!--
			/* B-O-F: PARTIALLY GOES TO FRONT */

			li.ff_listItem {
				width: 100%;
				/*background-color:#3F0;*/
			}

			li.ff_listItem .ff_div {
				width: auto;
				background-color: #eaf3fa;
				/*border: solid 2px red;*/
				float: left;
			}

			/* E-O-F: PARTIALLY GOES TO FRONT */

			/* SYSTEM STYLES */
			.ui-resizable-handle,
			.ui-resizable,
			.ui-resizable-se,
			.ui-wrapper {
				/*border: 1px #000000 solid;*/
				float: left;
				width: auto;
			}

			.bfOptionsTextInput {
				width: 100%;
			}

			#main-container-easymode {
				height: 100%;
			}

			#menutab {
				float: left;
				width: 300px;
				height: 100%;
			}

			#form-area-easymode {
				padding-left: 310px;
			}

			#trashcan {
				list-style: none;
			}

			#trashcan-box {
				height: 100px;
				background: #fbfbfb url(<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/themes/easymode/i/trash-here.png'; ?>) center no-repeat;
				margin-bottom: 10px;
			}

			#trashcan-box ul#trashcan {
				width: 100%;
				height: 100px;
				overflow: auto;
				padding: 0;
				margin: 0;
				float: left;
			}

			.ff_dragBox {
				width: 10px;
				height: 10px;
				cursor: move;
				float: left;
				background-image: url("<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/hand_icon.png' ?>");
			}

			.draggableElement {
				padding: 2px;
			}
			-->
		</style>

		<!-- TEMPLATE STYLES -->
		<style>
			<!--
			.droppableArea {
				list-style: none;
				padding: 5px;
				margin: 0;
				height: 600px;
				width: 100%;
				min-width: 700px !important;
				overflow: auto;
				background: #f6f6f6 url(<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/themes/easymode/i/drag-here.png'; ?>) center no-repeat;
				border: 2px dashed #ccc;
				width: auto;
			}

			.droppableArea li {
				margin: 0 0 0 0;
				padding-bottom: 0px;
				width: 100%;
			}

			.ff_label {
				float: left;
			}

			.ff_elem {
				float: right;
				border-width: 0px;
				border-color:
			}
			-->
		</style>

		<link rel="stylesheet"
			href="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/themes/easymode/easymode.all.css'; ?>"
			type="text/css" media="screen" title="Flora (Default)">
		<?php require_once(JPATH_SITE . '/administrator/components/com_breezingforms/admin/easymode-js.php'); ?>

		<div>
			<?php echo JToolBarHelper::custom('save', 'save.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_EASYMODE_SAVE'), false); ?>
			<?php
			if ($formId != 0) {
				JToolBarHelper::custom('editform', 'edit.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_EASYMODE_FORM_EDIT'), false);
				JToolBarHelper::custom('preview', 'publish.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_EASYMODE_PREVIEW'), false);
				JToolBarHelper::custom('preview_site', 'publish.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMS_SITE_PREVIEW'), false);
			}
			?>
			<?php JToolBarHelper::custom('close', 'cancel.png', 'cancel_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_QUICKMODE_CLOSE'), false); ?>
			<form action="index.php" method="post" id="adminForm" name="adminForm">
				<input type="hidden" name="option" value="com_breezingforms" />
				<input type="hidden" name="act" value="easymode" />
				<input type="hidden" name="templateCode" value="" />

				<input type="hidden" name="areas" value="" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="form" value="<?php echo $formId; ?>" />
				<input type="hidden" name="formName" value="<?php echo $formName; ?>" />
				<input type="hidden" name="page" value="<?php echo $page ?>" />
				<input type="hidden" name="pages" value="<?php echo $pages ?>" />
			</form>
		</div>

		<div style="clear:both;"></div>


		<form name="bfForm" onsubmit="return false;">

			<div id="main-container-easymode">

				<div id="menutab" class="flora">
					<ul>
						<li><a href="#fragment-1" onclick="app.refreshTemplateBox();app.refreshBatchOptions();"><span>
									<div class="tab-items">
										<?php echo BFText::_('COM_BREEZINGFORMS_ITEMS') ?>
									</div>
								</span></a></li>
						<li><a href="#fragment-2" onclick="app.refreshTemplateBox();app.refreshBatchOptions();"><span>
									<div class="tab-element">
										<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT') ?>
									</div>
								</span></a></li>
						<li><a href="#fragment-3" onclick="app.refreshTemplateBox();app.refreshBatchOptions();"><span>
									<div class="tab-form">
										<?php echo BFText::_('COM_BREEZINGFORMS_FORM') ?>
									</div>
								</span></a></li>
					</ul>

					<div class="t">

						<div class="t">
							<div class="t"></div>
						</div>
					</div>

					<div class="m">

						<div id="fragment-1">
							<div>

								<ul id="nestedaccordion" class="ui-accordion-container" style="width: 275px;">
									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_BASIC') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div>


											<div class="draggableElement" id="bfStaticText" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-statictext" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_STATIC_TEXT') ?>
												</span>
											</div>

											<div class="draggableElement" id="bfTextfield" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-textfield" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_TEXTFIELD') ?>
												</span>

											</div>

											<div class="draggableElement" id="bfTextarea" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-textarea" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_TEXTAREA') ?>
												</span>
											</div>

											<div class="draggableElement" id="bfCheckbox" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-checkboxx" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_CHECKBOX') ?>
												</span>
											</div>

											<div class="draggableElement" id="bfRadio" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-radio" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_RADIO') ?>
												</span>

											</div>

											<div class="draggableElement" id="bfSelect" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-select" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_SELECT') ?>
												</span>
											</div>

											<div class="draggableElement" id="bfFile" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-filee" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_FILE') ?>
												</span>
											</div>

											<div class="draggableElement" id="bfTooltip" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-tooltip" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_TOOLTIP') ?>
												</span>

											</div>

											<div class="draggableElement" id="bfIcon" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-icon" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_ICON') ?>
												</span>
											</div>

											<div class="draggableElement" id="bfSubmitButton" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-submitbutton" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_SUBMITBUTTON') ?>
												</span>
											</div>

											<div class="draggableElement" id="bfImageButton" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-imagebutton" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_IMAGE_BUTTON') ?>
												</span>

											</div>

											<div class="draggableElement" id="bfHidden" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-hiddeninput" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_HIDDEN_INPUT') ?>
												</span>
											</div>


										</div>
									</li>
									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_SPECIAL') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div>


											<div class="draggableElement" id="bfCaptcha" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-captcha" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_CAPTCHA') ?>
												</span>
											</div>

											<div class="draggableElement" id="bfCalendar" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-calendarr" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_CALENDAR') ?>
												</span>
											</div>

											<div class="draggableElement" id="bfPayPal" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-paypal" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_PAYPAL') ?>
												</span>
											</div>


											<div class="draggableElement" id="bfSofortueberweisung" style="z-index:1000;"
												onMouseover="this.style.backgroundColor='#eaf3fa';"
												onMouseout="this.style.backgroundColor='white';">
												<span class="icon-sofort" style="width: 100px !important;">
													<?php echo BFText::_('COM_BREEZINGFORMS_SOFORTUEBERWEISUNG') ?>
												</span>
											</div>

										</div>
									</li>
								</ul>
							</div>
						</div>
						<div id="fragment-2">

							<div>
								<ul id="nestedaccordion2" class="ui-accordion-container" style="width: 275px;">
									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_OPTIONS') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div>
											<div id="bfOptionsWrapper" style="display:none;">
												<br />
												<span id="bfOptionsSaveMessage" style="visibility:hidden;display:none"></span>
												<!-- Calendar -->

												<div id="bfCalendarOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_LINKTEXT_MAY_CONTAIN_HTML') ?>:
													<br />
													<textarea class="bfOptionsTextInput" id="bfCalendarText"></textarea>
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_FORMAT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfCalendarFormat"
														value="" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_CONNECT_WITH_FIELD_NAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfCalendarConnectWith"
														value="" />
												</div>
												<!-- Captcha -->
												<div id="bfCaptchaOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfCaptchaWidth"
														value="" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfCaptchaHeight"
														value="" />
												</div>
												<!-- Label -->
												<div id="bfLabelOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_CONTENT_MAY_CONTAIN_HTML') ?>:
													<br />
													<textarea class="bfOptionsTextInput" id="bfLabelContent"
														rows="10"></textarea>

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfLabelWidth" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfLabelHeight" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ON_TOP') ?>

													<br />
													<input type="checkbox" id="bfLabelOnTop" value="" />
												</div>
												<!-- Static Text -->
												<div id="bfStaticTextOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfStaticTextTitle"
														value="" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_CONTENT_MAY_CONTAIN_HTML') ?>:
													<br />
													<textarea class="bfOptionsTextInput" id="bfStaticTextContent"
														rows="10"></textarea>
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfStaticTextWidth"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfStaticTextHeight"
														value="" />
												</div>
												<!-- Text -->
												<div id="bfTextfieldOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTextfieldTitle"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfTextfieldName"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_VALUE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTextfieldValue"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_PASSWORD') ?>
													<br />
													<input type="checkbox" id="bfTextfieldPassword" value="" />

													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK') ?>
													<br />
													<input type="checkbox" id="bfTextfieldMailback" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK_AS_SENDER') ?>
													<br />
													<input type="checkbox" id="bfTextfieldMailbackAsSender" value="" />

													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_MAILBACKFILE_IF_IS_MAILBACK_A_FILE_FROM_THIS_SERVER_PATH_IS_SENT_TO_THE_MAILBACK_ADDRESS') ?>
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTextfieldMailbackfile"
														style="width:100%" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfTextfieldWidth"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTextfieldHeight"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_MAXLENGTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTextfieldMaxlength"
														value="" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_DISABLE') ?>
													<br />
													<input type="checkbox" id="bfTextfieldDisable" value="disable" />
												</div>
												<!-- Textarea -->
												<div id="bfTextareaOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTextareaTitle"
														value="" />

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTextareaName"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_VALUE') ?>:
													<br />
													<textarea class="bfOptionsTextInput" id="bfTextareaValue"></textarea>
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTextareaWidth"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTextareaHeight"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_DISABLE') ?>
													<br />

													<input type="checkbox" id="bfTextareaDisable" value="disable" />
												</div>
												<!-- Checkbox -->
												<div id="bfCheckboxOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfCheckboxTitle"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfCheckboxName"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_CHECKED') ?>:
													<br />
													<input type="checkbox" id="bfCheckboxChecked" value="" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK_ACCEPT') ?>:
													<br />
													<input type="checkbox" id="bfCheckboxMailbackAccept" value="" />

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK_ACCEPT__CONNECT_WITH_NAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfCheckboxMailbackAcceptConnectWith" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_VALUE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfCheckboxValue"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfCheckboxWidth"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfCheckboxHeight"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_DISABLE') ?>
													<br />
													<input type="checkbox" id="bfCheckboxDisable" value="disable" />

												</div>
												<!-- Radio -->
												<div id="bfRadioOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfRadioTitle" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfRadioName" value="" />

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_CHECKED') ?>:
													<br />
													<input type="checkbox" id="bfRadioChecked" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_VALUE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfRadioValue" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfRadioWidth" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfRadioHeight" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_DISABLE') ?>
													<br />
													<input type="checkbox" id="bfRadioDisable" value="disable" />

												</div>
												<!-- Select -->
												<div id="bfSelectOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSelectTitle" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSelectName" value="" />

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_MULTIPLE') ?>:
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_YES') ?> <input type="radio"
														name="bfSelectMultiple" id="bfSelectMultipleYes" value="1" />
													<?php echo BFText::_('COM_BREEZINGFORMS_NO') ?> <input type="radio"
														name="bfSelectMultiple" id="bfSelectMultipleNo" value="0" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_OPTIONS') ?>:
													<br />

													<textarea class="bfOptionsTextInput" id="bfSelectOpts" rows="10"></textarea>
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK') ?>
													<br />
													<input type="checkbox" id="bfSelectMailback" value="" />
													<br />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSelectWidth" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSelectHeight"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_DISABLE') ?>
													<br />

													<input type="checkbox" id="bfSelectDisable" value="disable" />
												</div>
												<!-- File -->
												<div id="bfFileOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfFileTitle" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfFileName" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ADD_TIMESTAMP_TO_FILENAME') ?>
													<br />
													<input type="checkbox" id="bfFileTimestamp" value="1" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_UPLOAD_DIRECTORY') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfFileUploadDirectory"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ALLOWED_FILE_EXTENSIONS') ?>
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfFileAllowedFileExtensions" value="" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ATTACH_FILE_TO_ADMIN_MAILS') ?>
													<br />

													<input type="checkbox" id="bfFileAttachToAdminMail" value="0" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ATTACH_FILE_TO_USER_MAILS') ?>
													<br />
													<input type="checkbox" id="bfFileAttachToUserMail" value="0" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfFileWidth" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfFileHeight" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_DISABLE') ?>
													<br />
													<input type="checkbox" id="bfFileDisable" value="disable" />

												</div>
												<!-- Icon -->
												<div id="bfIconOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_CAPTION_MAY_CONTAIN_HTML') ?>:
													<br />
													<textarea class="bfOptionsTextInput" id="bfIconCaption"></textarea>
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfIconWidth" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfIconHeight" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ICON_IMAGE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfIconImage" value="" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_ICON_IMAGE_OVER') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfIconImageOver"
														value="" />
												</div>
												<!-- Image Button -->
												<div id="bfImageButtonOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfImageButtonTitle"
														value="" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfImageButtonName"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfImageButtonWidth"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfImageButtonHeight"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_VALUE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfImageButtonValue"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_IMAGE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfImageButtonImage"
														value="" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_DISABLE') ?>
													<br />
													<input type="checkbox" id="bfImageButtonDisable" value="disable" />
												</div>
												<!-- Submit Button -->
												<div id="bfSubmitButtonOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSubmitButtonTitle"
														value="" />

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSubmitButtonName"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSubmitButtonWidth"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfSubmitButtonHeight"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_VALUE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSubmitButtonValue"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_DISABLE') ?>
													<br />
													<input type="checkbox" id="bfSubmitButtonDisable" value="disable" />

												</div>
												<!-- Tooltip -->
												<div id="bfTooltipOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTooltipTitle"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTooltipName" value="" />

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_TYPE') ?>:
													<br />
													<input type="radio" name="bfTooltipType" id="bfTooltipTypeInfo"
														value="info" /> <img
														src="<?php echo Uri::root(); ?>includes/js/ThemeOffice/tooltip.png" />
													<input type="radio" name="bfTooltipType" id="bfTooltipTypeWarning"
														value="warning" /> <img
														src="<?php echo Uri::root(); ?>includes/js/ThemeOffice/warning.png" />
													<input type="radio" name="bfTooltipType" id="bfTooltipTypeCustom"
														value="warning" />
													<?php echo BFText::_('COM_BREEZINGFORMS_CUSTOM') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTooltipCustomImage"
														value="" />

													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_TEXT') ?>:
													<br />
													<textarea class="bfOptionsTextInput" id="bfTooltipText"></textarea>
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfTooltipWidth"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfTooltipHeight"
														value="" />
													<br />
												</div>
												<!-- PayPal -->
												<div id="bfPayPalOptions" class="bfOptions"
													style="visibility:hidden;display:none">

													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalTitle" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalName" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_TESTACCOUNT') ?>:
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_YES') ?><input type="radio"
														id="bfPayPalTestaccountYes" name="bfPayPalTestaccount" value="1" />
													<?php echo BFText::_('COM_BREEZINGFORMS_NO') ?><input type="radio"
														id="bfPayPalTestaccountNo" name="bfPayPalTestaccount" value="0"
														checked="checked" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ACCOUNT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalBusiness"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ACCOUNTTOKEN_GET_IT_FROM_PAYPAL') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfPayPalToken" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_TESTACCOUNT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalTestBusiness"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_TESTACCOUNTTOKEN_GET_IT_FROM_PAYPAL_SANDBOX') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalTestToken"
														value="" />
													<br />

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_USE_IPN') ?>:
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_YES') ?><input type="radio"
														id="bfPayPalUseIpnYes" name="bfPayPalUseIpn" value="1" />
													<?php echo BFText::_('COM_BREEZINGFORMS_NO') ?><input type="radio"
														id="bfPayPalUseIpnNo" name="bfPayPalUseIpn" value="0"
														checked="checked" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_DOWNLOADABLEFILE') ?>:
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_YES') ?><input type="radio"
														id="bfPayPalDownloadableFileYes" name="bfPayPalDownloadableFile"
														value="1" />

													<?php echo BFText::_('COM_BREEZINGFORMS_NO') ?><input type="radio"
														id="bfPayPalDownloadableFileNo" name="bfPayPalDownloadableFile"
														value="0" checked="checked" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_FILEPATH_PLEASE_CHMOD_700_THE_FILE_USING_YOUR_FTP_CLIENT_OR_PUT_IT_OUTSIDE_OF_YOUR_WEBFOLDER') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalFilepath"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_FILE_DOWNLOAD_TRIES') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalFileDownloadTries"
														value="1" />

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ITEMNAME') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalItemname"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ITEMNUMBER') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalItemnumber"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_AMOUNT') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfPayPalAmount"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_TAX') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalTax" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_THANKYOUPAGE_IF_NOT_DOWNLOADABLE_FILE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalThankYouPage"
														value="<?php echo Uri::root() ?>" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_LOCALE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalLocale"
														value="us" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_CURRENCYCODE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalCurrencyCode"
														value="USD" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_PAYPALIMAGE') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfPayPalImage"
														value="http://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalWidth" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfPayPalHeight"
														value="" />
													<br />

												</div>
												<br />
												<!-- SofortÃ¼berweisung -->
												<div id="bfSofortueberweisungOptions" class="bfOptions"
													style="visibility:hidden;display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_TITLE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSofortueberweisungTitle"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfSofortueberweisungName"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_USERID') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungUserId" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_PROJECTID') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungProjectId" value="" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_PROJECTPASSWORD') ?>:
													<br />
													<input type="password" class="bfOptionsTextInput"
														id="bfSofortueberweisungProjectPassword" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_SEND_PAYMENT_SUCCESS_MESSAGE_TO_MAILBACK_ADDRESSES') ?>:
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_YES') ?><input type="radio"
														id="bfSofortueberweisungMailbackYes" name="bfSofortueberweisungMailback"
														value="1" />
													<?php echo BFText::_('COM_BREEZINGFORMS_NO') ?><input type="radio"
														id="bfSofortueberweisungMailbackNo" name="bfSofortueberweisungMailback"
														value="0" checked="checked" />
													<br />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_DOWNLOADABLEFILE') ?>:
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_YES') ?><input type="radio"
														id="bfSofortueberweisungDownloadableFileYes"
														name="bfSofortueberweisungDownloadableFile" value="1" />
													<?php echo BFText::_('COM_BREEZINGFORMS_NO') ?><input type="radio"
														id="bfSofortueberweisungDownloadableFileNo"
														name="bfSofortueberweisungDownloadableFile" value="0"
														checked="checked" />
													<br />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_FILEPATH_PLEASE_CHMOD_700_THE_FILE_USING_YOUR_FTP_CLIENT_OR_PUT_IT_OUTSIDE_OF_YOUR_WEBFOLDER') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungFilepath" value="" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_FILE_DOWNLOAD_TRIES') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungFileDownloadTries" value="1" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_REASON_1') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungReason1" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_REASON_2') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungReason2" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_AMOUNT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungAmount" value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_THANKYOUPAGE_IF_NOT_DOWNLOADABLE_FILE') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungThankYouPage"
														value="<?php echo Uri::root() ?>" />
													<br />

													<?php echo BFText::_('COM_BREEZINGFORMS_LANGUAGE_ID') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungLanguageId" value="DE" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_CURRENCY_ID') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungCurrencyId" value="EUR" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_SOFORTUEBERWEISUNGIMAGE') ?>:
													<br />

													<input type="text" class="bfOptionsTextInput" id="bfSofortueberweisungImage"
														value="<?php echo Uri::root() ?>components/com_breezingforms/images/200x65px.png" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfSofortueberweisungWidth"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput"
														id="bfSofortueberweisungHeight" value="" />
													<br />

												</div>
												<br />
												<div id="bfGlobalOptions" style="display:none">
													<?php echo BFText::_('COM_BREEZINGFORMS_PADDING') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfOptionsPadding"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_MARGIN') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfOptionsMargin"
														value="" />

													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfOptionsOrder"
														value="" />
													<br />
													<?php echo BFText::_('COM_BREEZINGFORMS_TABINDEX_NUMBER') ?>:
													<br />
													<input type="text" class="bfOptionsTextInput" id="bfOptionsTabIndex"
														value="" />
													<br />
												</div>

												<input type="submit" value="<?php echo BFText::_('COM_BREEZINGFORMS_UPDATE') ?>"
													id="bfSaveOptionsButton"
													style="visibility:hidden;display:none;width:100%;" />
												<br />
												<br />
												<input type="submit" value="<?php echo BFText::_('COM_BREEZINGFORMS_REMOVE') ?>"
													id="bfRemoveLabelButton"
													style="visibility:hidden;display:none;width:100%;" />
												<br />
												<br />
											</div>
										</div>
									</li>

									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_ACTIONS') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div id="bfActions" style="display:none">
											<br />
											<select id="bfBesideCreationButton" style="width:100%"
												onchange="app.createElementBesideByType(app.optionElement, this)">
												<option value="">
													<?php echo BFText::_('COM_BREEZINGFORMS_CREATE_ELEMENT_BESIDE') ?>
												</option>
												<option value="bfStaticText">
													<?php echo BFText::_('COM_BREEZINGFORMS_STATIC_TEXT') ?>
												</option>
												<option value="bfTextfield">
													<?php echo BFText::_('COM_BREEZINGFORMS_TEXTFIELD') ?>
												</option>
												<option value="bfTextarea">
													<?php echo BFText::_('COM_BREEZINGFORMS_TEXTAREA') ?>
												</option>

												<option value="bfCheckbox">
													<?php echo BFText::_('COM_BREEZINGFORMS_CHECKBOX') ?>
												</option>
												<option value="bfRadio">
													<?php echo BFText::_('COM_BREEZINGFORMS_RADIO') ?>
												</option>
												<option value="bfSelect">
													<?php echo BFText::_('COM_BREEZINGFORMS_SELECT') ?>
												</option>
												<option value="bfFile">
													<?php echo BFText::_('COM_BREEZINGFORMS_FILE') ?>
												</option>
												<option value="bfTooltip">
													<?php echo BFText::_('COM_BREEZINGFORMS_TOOLTIP') ?>
												</option>
												<option value="bfIcon">
													<?php echo BFText::_('COM_BREEZINGFORMS_ICON') ?>
												</option>
												<option value="bfSubmitButton">
													<?php echo BFText::_('COM_BREEZINGFORMS_SUBMIT_BUTTON') ?>
												</option>
												<option value="bfImageButton">
													<?php echo BFText::_('COM_BREEZINGFORMS_IMAGE_BUTTON') ?>
												</option>
												<option value="bfCaptcha">
													<?php echo BFText::_('COM_BREEZINGFORMS_CAPTCHA') ?>
												</option>

												<option value="bfCalendar">
													<?php echo BFText::_('COM_BREEZINGFORMS_CALENDAR') ?>
												</option>
												<option value="bfPayPal">
													<?php echo BFText::_('COM_BREEZINGFORMS_PAYPAL') ?>
												</option>
												<option value="bfSofortueberweisung">
													<?php echo BFText::_('COM_BREEZINGFORMS_SOFORTUEBERWEISUNG') ?>
												</option>
											</select>
											<br />
											<br />
											<input type="submit" id="bfElementRemoveButton"
												onclick="app.removeElement(app.optionElement)"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_REMOVE_ELEMENT') ?>"
												style="visibility:hidden;display:none;width:100%;">
											<br />
											<br />

											<input type="submit" id="bfElementMoveLeft"
												onclick="app.moveElement(app.optionElement, 'prev')"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_MOVE_LEFT') ?>"
												style="width:49%;visibility:hidden;display:none">
											<input type="submit" id="bfElementMoveRight"
												onclick="app.moveElement(app.optionElement, 'next')"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_MOVE_RIGHT') ?>"
												style="width:49%;visibility:hidden;display:none">
											<br />
											<br />
										</div>
									</li>
									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_INIT_SCRIPT') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div>

											<div id="bfInitScript" style="display:none">
												<br />
												<span class="bfScriptsSaveMessage" style="display:none"></span>
												<?php echo BFText::_('COM_BREEZINGFORMS_TYPE') ?>:
												<?php echo BFText::_('COM_BREEZINGFORMS_NONE') ?> <input
													onclick="JQuery('#bfInitScriptFlags').css('display','none');JQuery('#bfInitScriptLibrary').css('display','none');JQuery('#bfInitScriptCustom').css('display','none');"
													type="radio" name="initType" id="bfInitTypeNone" class="bfInitType"
													value="0" />
												<?php echo BFText::_('COM_BREEZINGFORMS_LIBRARY') ?> <input
													onclick="JQuery('#bfInitScriptFlags').css('display','');JQuery('#bfInitScriptLibrary').css('display','');JQuery('#bfInitScriptCustom').css('display','none');"
													type="radio" name="initType" id="bfInitTypeLibrary" class="bfInitType"
													value="1" />
												<?php echo BFText::_('COM_BREEZINGFORMS_CUSTOM') ?> <input
													onclick="JQuery('#bfInitScriptFlags').css('display','');JQuery('#bfInitScriptLibrary').css('display','none');JQuery('#bfInitScriptCustom').css('display','');"
													type="radio" name="initType" id="bfInitTypeCustom" class="bfInitType"
													value="2" />

												<div id="bfInitScriptFlags" style="display:none">

													<hr />

													<input type="checkbox" id="script1flag1" class="script1flag"
														name="script1flag1" value="1" /><label for="script1flag1">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_FORMENTRY'); ?>
													</label>
													<input type="checkbox" id="script1flag2" class="script1flag"
														name="script1flag2" value="1" /><label for="script1flag2">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGEENTRY'); ?>
													</label>
												</div>

												<div id="bfInitScriptLibrary" style="display:none">
													<hr />
													<?php echo BFText::_('COM_BREEZINGFORMS_SCRIPT') ?>: <select
														id="bfInitScriptSelection"></select>

												</div>

												<div id="bfInitScriptCustom" style="display:none">
													<hr />
													<div class="btn" style="cursor: pointer;"
														onclick="createInitCode(app.optionElement)">
														<?php echo BFText::_('COM_BREEZINGFORMS_CREATE_CODE_FRAMEWORK') ?>
													</div>
													<textarea name="script1code" id="script1code" rows="10" style="width:100%"
														wrap="off"></textarea>
												</div>

												<hr />

												<input id="bfInitButton" type="submit" value="update" style="width:100%" />


												<br />
												<br />
											</div>
										</div>
									</li>
									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_ACTION_SCRIPT') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div>
											<span class="bfScriptsSaveMessage" style="display:none"></span>

											<div id="bfActionScript" style="display:none">
												<?php echo BFText::_('COM_BREEZINGFORMS_TYPE') ?>:
												<?php echo BFText::_('COM_BREEZINGFORMS_NONE') ?> <input
													onclick="JQuery('#bfActionScriptFlags').css('display','none');JQuery('#bfActionScriptLibrary').css('display','none');JQuery('#bfActionScriptCustom').css('display','none');"
													type="radio" name="actionType" name="actionType" id="bfActionTypeNone"
													class="bfActionType" value="0" />
												<?php echo BFText::_('COM_BREEZINGFORMS_LIBRARY') ?> <input
													onclick="JQuery('#bfActionScriptFlags').css('display','');JQuery('#bfActionScriptLibrary').css('display','');JQuery('#bfActionScriptCustom').css('display','none');"
													type="radio" name="actionType" id="bfActionTypeLibrary" class="bfActionType"
													value="1" />
												<?php echo BFText::_('COM_BREEZINGFORMS_CUSTOM') ?> <input
													onclick="JQuery('#bfActionScriptFlags').css('display','');JQuery('#bfActionScriptLibrary').css('display','none');JQuery('#bfActionScriptCustom').css('display','');"
													type="radio" name="actionType" id="bfActionTypeCustom" class="bfActionType"
													value="2" />

												<div id="bfActionScriptFlags" style="display:none">
													<hr />
													<?php echo BFText::_('COM_BREEZINGFORMS_ACTIONS') ?>:
													<br />
													<input style="display:none" type="checkbox" class="script2flag"
														id="script2flag1" name="script2flag1" value="1" /><label
														style="display:none" class="script2flagLabel" id="script2flag1Label"
														for="script2flag1">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CLICK'); ?>
													</label>

													<input style="display:none" type="checkbox" class="script2flag"
														id="script2flag2" name="script2flag2" value="1" /><label
														style="display:none" class="script2flagLabel" id="script2flag2Label">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BLUR'); ?>
													</label>
													<input style="display:none" type="checkbox" class="script2flag"
														id="script2flag3" name="script2flag3" value="1" /><label
														style="display:none" class="script2flagLabel" id="script2flag3Label">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CHANGE'); ?>
													</label>
													<input style="display:none" type="checkbox" class="script2flag"
														id="script2flag4" name="script2flag4" value="1" /><label
														style="display:none" class="script2flagLabel" id="script2flag4Label">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_FOCUS'); ?>
													</label>
													<input style="display:none" type="checkbox" class="script2flag"
														id="script2flag5" name="script2flag5" value="1" /><label
														style="display:none" class="script2flagLabel" id="script2flag5Label">
														<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELECTION'); ?>
													</label>

												</div>

												<div id="bfActionScriptLibrary" style="display:none">
													<hr />

													<?php echo BFText::_('COM_BREEZINGFORMS_SCRIPT') ?>: <select
														id="bfActionsScriptSelection"></select>
												</div>

												<div id="bfActionScriptCustom" style="display:none">
													<hr />
													<div class="btn" style="cursor: pointer;"
														onclick="createActionCode(app.optionElement)">
														<?php echo BFText::_('COM_BREEZINGFORMS_CREATE_CODE_FRAMEWORK') ?>
													</div>
													<textarea name="script2code" id="script2code" rows="10" style="width:100%"
														wrap="off"></textarea>
												</div>

												<hr />


												<input id="bfActionButton" type="submit"
													value="<?php echo BFText::_('COM_BREEZINGFORMS_UPDATE') ?>"
													style="width:100%" />

												<br />
												<br />
											</div>
										</div>
									</li>
									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_VALIDATION_SCRIPT') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div>

											<span class="bfScriptsSaveMessage" style="display:none"></span>
											<div id="bfValidationScript" style="display:none">

												<?php echo BFText::_('COM_BREEZINGFORMS_TYPE') ?>:
												<?php echo BFText::_('COM_BREEZINGFORMS_NONE') ?> <input
													onclick="JQuery('#bfValidationScriptFlags').css('display','none');JQuery('#bfValidationScriptLibrary').css('display','none');JQuery('#bfValidationScriptCustom').css('display','none');"
													type="radio" name="validationType" id="bfValidationTypeNone"
													class="bfValidationType" value="0" />
												<?php echo BFText::_('COM_BREEZINGFORMS_LIBRARY') ?> <input
													onclick="JQuery('#bfValidationScriptFlags').css('display','');JQuery('#bfValidationScriptLibrary').css('display','');JQuery('#bfValidationScriptCustom').css('display','none');"
													type="radio" name="validationType" id="bfValidationTypeLibrary"
													class="bfValidationType" value="1" />
												<?php echo BFText::_('COM_BREEZINGFORMS_CUSTOM') ?> <input
													onclick="JQuery('#bfValidationScriptFlags').css('display','');JQuery('#bfValidationScriptLibrary').css('display','none');JQuery('#bfValidationScriptCustom').css('display','');"
													type="radio" name="validationType" id="bfValidationTypeCustom"
													class="bfValidationType" value="2" />

												<div id="bfValidationScriptFlags" style="display:none">
													<hr />

													<?php echo BFText::_('COM_BREEZINGFORMS_ERROR_MESSAGE') ?>: <input
														type="text" style="width:100%" maxlength="255" class="script3msg"
														id="script3msg" name="script3msg" value="" class="inputbox" />
												</div>

												<div id="bfValidationScriptLibrary" style="display:none">
													<hr />
													<?php echo BFText::_('COM_BREEZINGFORMS_SCRIPT') ?>: <select
														id="bfValidationScriptSelection"></select>
												</div>

												<div id="bfValidationScriptCustom" style="display:none">
													<hr />

													<div class="btn" style="cursor: pointer;"
														onclick="createValidationCode(app.optionElement)">
														<?php echo BFText::_('COM_BREEZINGFORMS_CREATE_CODE_FRAMEWORK') ?>
													</div>
													<textarea name="script3code" id="script3code" rows="10" style="width:100%"
														wrap="off"></textarea>
												</div>

												<hr />

												<input id="bfValidationButton" type="submit"
													value="<?php echo BFText::_('COM_BREEZINGFORMS_UPDATE') ?>"
													style="width:100%" />

												<br />
												<br />

											</div>

										</div>
									</li>
								</ul>
							</div>
						</div>
						<div id="fragment-3">
							<div>

								<ul id="nestedaccordion3" class="ui-accordion-container" style="width: 275px;">

									<li>

										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_PAGES') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div>
											<br />
											<?php echo BFText::_('COM_BREEZINGFORMS_CURRENT_PAGE') ?>: <span
												id="bfCurrentPage"></span>
											<br />
											<br />
											<input type="submit"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_CREATE_NEW_PAGE') ?>"
												id="bfCreatePage" style="width:100%" />
											<br />

											<br />
											<select id="bfGoToPage" style="width:100%">
												<option value="-1">
													<?php echo BFText::_('COM_BREEZINGFORMS_GO_TO_PAGE') ?>
												</option>
											</select>
											<br />
											<br />
											<select id="bfMoveThisPageTo" style="width:100%">
												<option value="-1">
													<?php echo BFText::_('COM_BREEZINGFORMS_MOVE_THIS_PAGE_TO') ?>
												</option>
											</select>

											<br />
											<br />
											<input type="submit"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_DELETE_THIS_PAGE') ?>"
												id="bfDeleteThisPage" style="width:100%" />
											<br />
											<br />
										</div>
									</li>

									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_HIDDEN_FIELDS') ?>
											<div class="ui-accordion-right"></div>
										</a>

										<div>
											<div id="bfHiddenFieldsOptions">

											</div>
										</div>
									</li>

									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_CODE') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div>
											<?php echo BFText::_('COM_BREEZINGFORMS_ATTENTION_CHANGE_THE_GENERATED_TEMPLATE_CODE_ON_YOUR_OWN_RISK_BEST_IS_TO_KEEP_THE_UL_TAGS_AND_THEIR_CONTENTS_AS_IS_AND_CHANGE_ONLY_THE_LAYOUT_AROUND_IF_NECESSARY_AND_NEVER_UPDATE_WHEN_YOU_HAVE_UNSAVED_ELEMENTS_IN_THE_EDITOR') ?>

											<br />
											<br />
											<textarea rows="10" style="width:100%;" id="bfTemplateBox" wrap="off"></textarea>
											<br />
											<input type="submit" id="bfUpdateTemplateButton"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_UPDATE') ?>"
												style="width:100%" />
											<br />
											<br />
										</div>

									</li>

									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_BATCH_OPTIONS') ?>
											<div class="ui-accordion-right"></div>
										</a>
										<div>
											<br />
											<?php echo BFText::_('COM_BREEZINGFORMS_LABELS') ?>
											<br />
											<select id="bfBatchLabels" multiple="multiple"
												style="width:100%;height:100px;"></select>
											<br />

											<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS') ?>
											<br />
											<select id="bfBatchElements" multiple="multiple"
												style="width:100%;height:100px;"></select>
											<br />
											<?php echo BFText::_('COM_BREEZINGFORMS_WIDTH') ?>
											<br />
											<input type="text" id="bfBatchWidth" value="" style="width:100%" />
											<br />
											<?php echo BFText::_('COM_BREEZINGFORMS_HEIGHT') ?>

											<br />
											<input type="text" id="bfBatchHeight" value="" style="width:100%" />
											<br />
											<?php echo BFText::_('COM_BREEZINGFORMS_PADDING') ?>
											<br />
											<input type="text" id="bfBatchPadding" value="" style="width:100%" />
											<br />
											<?php echo BFText::_('COM_BREEZINGFORMS_MARGIN') ?>
											<br />

											<input type="text" id="bfBatchMargin" value="" style="width:100%" />
											<br />
											<input type="submit" id="bfBatchButton"
												value="<?php echo BFText::_('COM_BREEZINGFORMS_UPDATE') ?>"
												style="width:100%" />
											<br />
											<br />
										</div>
									</li>

									<li>
										<a href='#'>
											<div class="ui-accordion-left"></div>
											<?php echo BFText::_('COM_BREEZINGFORMS_MISC') ?>
											<div class="ui-accordion-right"></div>
										</a>

										<div>
											<br />
											<?php echo BFText::_('COM_BREEZINGFORMS_PIXEL_RASTER') ?>
											<br />
											<input type="text" id="bfPixelRaster" value="1" style="width:100%" />
											<br />
											<input type="submit" value="update" id="bfUpdatePixelRaster" style="width:100%" />
											<br />
											<br />

											<br />
										</div>
									</li>

								</ul>

							</div>
						</div>
						<div class="clear"></div>
					</div>
					<div class="b">

						<div class="b">
							<div class="b"></div>
						</div>
					</div>

					<br />
					<div id="easymode-trashcan">

						<span class="icon-trashcan">
							<?php echo BFText::_('COM_BREEZINGFORMS_TRASH_CAN') ?>
						</span>

						<div id="trashcan-box">

							<div class="t">

								<div class="t">
									<div class="t"></div>
								</div>
							</div>
							<div class="m">
								<ul id="trashcan">
								</ul>

								<div class="clr"></div>
							</div>

							<div class="b">
								<div class="b">
									<div class="b"></div>
								</div>
							</div>

						</div>
					</div><!-- easymode-trashcan end -->

				</div>

				<div id="form-area-easymode">

					<div id="bfTemplate">
						<?php if ($templateCode == ''): ?>
							<ul class="droppableArea" id="drop1"></ul>
						<?php endif; ?>
						<?php if ($templateCode != ''): ?>
							<?php echo $templateCode; ?>
						<?php endif; ?>
					</div>
				</div> <!-- form-area-easymode -->
				<div class="clear"></div>
			</div>

		</form>
		<?php
	}
}

?>