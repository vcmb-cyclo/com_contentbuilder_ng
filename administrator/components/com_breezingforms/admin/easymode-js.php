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

HTMLHelper::_('bootstrap.modal');


?>

<script>var moobackup = $;</script>
<script>var moobackup2 = $$;</script>

<script type="text/javascript"
	src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/'; ?>jq.js"></script>
<script type="text/javascript"
	src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/'; ?>jq-ui.min.js"></script>
<script type="text/javascript"
	src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/plugins/bas'; ?>e64.js"></script>
<script type="text/javascript"
	src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/plugins/'; ?>json.js"></script>
<script type="text/javascript"
	src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/plugins/'; ?>md5.js"></script>

<script>$ = moobackup;</script>
<script>$$ = moobackup2;</script>

<script type="text/javascript">
	var app = null;

	String.prototype.bfendsWith = function (suffix) {
		return this.match(suffix + "$") == suffix;
	};

	function BF_EasyModeApp() {
		JQuery("link").each(function () {
			// jquery easy workaround
			var _xj = 'j';
			var _xq = 'q';
			var _xu = 'u';
			var _xe = 'e';
			var _xr = 'r';
			var _xy = 'y';
			if (JQuery(this).attr('href').bfendsWith(_xj + _xq + _xu + _xe + _xr + _xy + '-ui.css')) {
				JQuery(this).attr('disabled', 'disabled');
				JQuery(this).remove();
			}
		});

		// to app scope
		var appScope = this;
		// the opacity for each draggable element
		this.opacity = 0.5;
		// the areaList contains all elements into all available areas. Structure: [ { area : droppableArea_instance, elements : [ Object_instance , ... ] } , ... ]
		this.areaList = [];
		// temp area list for the trashcan
		this.trashcanAreaList = [];
		// the actually selected element to change its options for
		this.optionElement = null;
		// all available element scripts
		this.elementScripts = <?php echo Zend_Json::encode($elementScripts) ?>

		this.captchaAdded = 0;

		this.pixelRaster = 1;

		this.setElementResizable = function (element) {
			JQuery('#' + element.id).resizable({
				grid: [appScope.pixelRaster, appScope.pixelRaster],
				//knobHandles : true,
				handles: "se",
				//minWidth: 5, 
				//minHeight: 5, 
				//ghost: true,
				//transparent: true
				start: function () {
					if (JQuery('#' + element.id).parent().attr('class') == 'ui-wrapper') {
						JQuery('#' + element.id).parent().css('padding-bottom', '0px');
						if (JQuery('#' + element.id).parent().children('.ui-resizable-se')[0]) {
							JQuery(JQuery('#' + element.id).parent().children('.ui-resizable-se')[0]).click(appScope.resizableElement);
						}
					}
					JQuery(this).css('border', '1px dotted #000');
				},
				stop: function () {
					//alert(JQuery(this).attr('class'));
					JQuery(this).css('border', 'none');
				}
			});

			if (JQuery('#' + element.id).parent().attr('class') == 'ui-wrapper') {
				JQuery('#' + element.id).parent().css('padding-bottom', '0px');
				if (JQuery('#' + element.id).parent().children('.ui-resizable-se')[0]) {
					JQuery(JQuery('#' + element.id).parent().children('.ui-resizable-se')[0]).click(appScope.resizableElement);
				}
			}
		};

		this.refreshTemplateBox = function () {
			JQuery('#bfTemplateBox').val(JQuery.trim(JQuery('#bfTemplate').get(0).innerHTML));
		};

		// first turn all pages on sucht that they can be initially processed by jquery
		JQuery('.bfPage').css('display', '');

		<?php if (isset($callbackParams['areas']) && trim($callbackParams['areas']) != '') { ?>

			var tmpAreas = <?php echo $callbackParams['areas'] ?>;

			for (var i = 0; i < tmpAreas.length; i++) {
				if (JQuery("#" + tmpAreas[i].id).get(0)) {
					if (!tmpAreas[i].pixelRaster) { // compat 686
						tmpAreas[i]['pixelRaster'] = 1;
					}
					if (i == 0) {
						appScope.pixelRaster = tmpAreas[i].pixelRaster;
						JQuery('#bfPixelRaster').val(appScope.pixelRaster);
					}
					this.areaList.push({ area: JQuery("#" + tmpAreas[i].id).get(0), elements: tmpAreas[i].elements, pixelRaster: tmpAreas[i].pixelRaster });
				}
			}

			for (var i = 0; i < appScope.areaList.length; i++) {
				for (var j = 0; j < appScope.areaList[i].elements.length; j++) {

					var element = appScope.areaList[i].elements[j];

					JQuery('#' + element.id).get(0).removeAttribute('onclick');
					JQuery('#' + element.id).get(0).removeAttribute('onblur');
					JQuery('#' + element.id).get(0).removeAttribute('onchange');
					JQuery('#' + element.id).get(0).removeAttribute('onfocus');
					JQuery('#' + element.id).get(0).removeAttribute('onselect');
					JQuery('#' + element.id).get(0).removeAttribute('onmouseover');
					JQuery('#' + element.id).get(0).removeAttribute('onmouseout');
					JQuery('#' + element.id).get(0).removeAttribute('tabindex');
					JQuery('#' + element.id).get(0).removeAttribute('checked');

					if (typeof element.tabIndex == 'undefined') { // compat 687
						element.tabIndex = -1;
					}

					JQuery('#' + element.id).css('border', 'none');

					if (element.internalType == 'bfCaptcha') {
						appScope.captchaAdded++;
					}

					var prevm = JQuery('#' + element.id).css('margin');
					var prevp = JQuery('#' + element.id).css('padding');
					var prevElementWidth = JQuery('#' + element.id).css('width');
					var prevElementHeight = JQuery('#' + element.id).css('height');
					if (element.internalType != 'bfHidden') {
						appScope.setElementResizable(element);
					}
					JQuery('#' + element.id).css('margin', prevm);
					JQuery('#' + element.id).css('padding', prevp);
					JQuery('#' + element.id).css('width', prevElementWidth);
					JQuery('#' + element.id).css('height', prevElementHeight);

					if (element.internalType == 'bfIcon') {
						JQuery(JQuery('#' + element.id).children('img')[0]).get(0).removeAttribute('onmouseover');
						JQuery(JQuery('#' + element.id).children('img')[0]).get(0).removeAttribute('onmouseout');
					}

					var labelsResult = JQuery('#' + element.id).parent().parent().children('.ff_label');
					if (labelsResult.length != 0) {
						for (var k = 0; k < labelsResult.length; k++) {
							prevm = JQuery('#' + labelsResult[k].id).css('margin');
							prevp = JQuery('#' + labelsResult[k].id).css('padding');
							prevElementWidth = JQuery('#' + labelsResult[k].id).css('width');
							prevElementHeight = JQuery('#' + labelsResult[k].id).css('height');
							appScope.setElementResizable(labelsResult[k]);
							JQuery('#' + labelsResult[k].id).css('margin', prevm);
							JQuery('#' + labelsResult[k].id).css('padding', prevp);
							JQuery('#' + labelsResult[k].id).css('width', prevElementWidth);
							JQuery('#' + labelsResult[k].id).css('height', prevElementHeight);

							var batchLabel = document.createElement('option');
							JQuery(batchLabel).val(labelsResult[k].id);
							JQuery(batchLabel).text(stripHTML(JQuery('#' + labelsResult[k].id).html()));
							JQuery('#bfBatchLabels').append(batchLabel);
						}
					}
					labelsResult = JQuery('#' + element.id).parent().children('.ff_label');
					if (labelsResult.length != 0) {
						for (var k = 0; k < labelsResult.length; k++) {
							prevm = JQuery('#' + labelsResult[k].id).css('margin');
							prevp = JQuery('#' + labelsResult[k].id).css('padding');
							prevElementWidth = JQuery('#' + labelsResult[k].id).css('width');
							prevElementHeight = JQuery('#' + labelsResult[k].id).css('height');
							appScope.setElementResizable(labelsResult[k]);
							JQuery('#' + labelsResult[k].id).css('margin', prevm);
							JQuery('#' + labelsResult[k].id).css('padding', prevp);
							JQuery('#' + labelsResult[k].id).css('width', prevElementWidth);
							JQuery('#' + labelsResult[k].id).css('height', prevElementHeight);

							var batchLabel = document.createElement('option');
							JQuery(batchLabel).val(labelsResult[k].id);
							JQuery(batchLabel).text(stripHTML(JQuery('#' + labelsResult[k].id).html()));
							JQuery('#bfBatchLabels').append(batchLabel);
						}
					}

					if (element.internalType != 'bfHidden') {
						var batchElement = document.createElement('option');
						JQuery(batchElement).val(element.id);
						JQuery(batchElement).text(element.name);
						JQuery('#bfBatchElements').append(batchElement);
					}
				}
			}
		<?php } ?>

		appScope.refreshTemplateBox();
		if (parseInt(document.adminForm.page.value) < 1) {
			document.adminForm.page.value = 1;
		}
		JQuery('#bfCurrentPage').get(0).innerHTML = document.adminForm.page.value + "/" + (parseInt(document.adminForm.pages.value) == 0 ? 1 : document.adminForm.pages.value);
		JQuery('.bfPage').css('display', 'none');
		JQuery('#bfPage' + document.adminForm.page.value).css('display', '');

		for (var i = 1; i <= parseInt(document.adminForm.pages.value); i++) {

			var option = document.createElement('option');
			JQuery(option).val(i);
			JQuery(option).text(i);

			var option2 = document.createElement('option');
			JQuery(option2).val(i);
			JQuery(option2).text(i);

			JQuery('#bfGoToPage').append(option);
			JQuery('#bfMoveThisPageTo').append(option2);
		}

		this.populateHiddenFieldsOptions = function () {

			JQuery('#bfHiddenFieldsOptions').empty();

			var element = null;
			var br = null;
			var hiddenFields = appScope.getElementsByType('bfHidden');

			for (var i = 0; i < hiddenFields.length; i++) {

				// element name header
				element = document.createElement('span');
				element.innerHTML = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME') ?>:';
				JQuery('#bfHiddenFieldsOptions').append(element);
				br = document.createElement('br');
				JQuery('#bfHiddenFieldsOptions').append(br);

				// element name
				element = document.createElement('input');
				element.setAttribute('type', 'text');
				element.setAttribute('id', 'name__HIDDEN__' + hiddenFields[i].id);
				element.setAttribute('class', 'bfHiddenOptionsName');
				element.setAttribute('value', hiddenFields[i].name);
				element.style.width = '100%';
				br = document.createElement('br');
				JQuery('#bfHiddenFieldsOptions').append(element);
				JQuery('#bfHiddenFieldsOptions').append(br);

				// element title header
				element = document.createElement('span');
				element.innerHTML = 'Title:';
				JQuery('#bfHiddenFieldsOptions').append(element);
				br = document.createElement('br');
				JQuery('#bfHiddenFieldsOptions').append(br);

				// element title
				element = document.createElement('input');
				element.setAttribute('type', 'text');
				element.setAttribute('id', 'title__HIDDEN__' + hiddenFields[i].id);
				element.setAttribute('class', 'bfHiddenOptionsTitle');
				element.setAttribute('value', hiddenFields[i].title);
				element.style.width = '100%';
				br = document.createElement('br');
				JQuery('#bfHiddenFieldsOptions').append(element);
				JQuery('#bfHiddenFieldsOptions').append(br);

				// element value header
				element = document.createElement('span');
				element.innerHTML = 'Value:';
				JQuery('#bfHiddenFieldsOptions').append(element);
				br = document.createElement('br');
				JQuery('#bfHiddenFieldsOptions').append(br);

				// element value
				element = document.createElement('input');
				element.setAttribute('type', 'text');
				element.setAttribute('id', 'value__HIDDEN__' + hiddenFields[i].id);
				element.setAttribute('class', 'bfHiddenOptionsValue');
				element.setAttribute('value', hiddenFields[i].options.value);
				element.style.width = '100%';
				br = document.createElement('br');
				JQuery('#bfHiddenFieldsOptions').append(element);
				JQuery('#bfHiddenFieldsOptions').append(br);

				element = document.createElement('span');
				element.innerHTML = 'Remove? ';
				JQuery('#bfHiddenFieldsOptions').append(element);
				element = document.createElement('input');
				element.setAttribute('type', 'checkbox');
				element.setAttribute('id', 'remove__HIDDEN__' + hiddenFields[i].id);
				element.setAttribute('class', 'bfHiddenOptionsDelete');
				//element.style.width = '100%';
				JQuery('#bfHiddenFieldsOptions').append(element);

				br = document.createElement('hr');
				JQuery('#bfHiddenFieldsOptions').append(br);
			}

			if (hiddenFields.length != 0) {

				// update button
				br = document.createElement('br');
				JQuery('#bfHiddenFieldsOptions').append(br);

				element = document.createElement('input');
				element.setAttribute('type', 'submit');
				element.setAttribute('value', 'update');
				element.style.width = '100%';
				JQuery(element).click(
					function () {

						var children = JQuery('#bfHiddenFieldsOptions').children('.bfHiddenOptionsValue');
						for (var i = 0; i < children.length; i++) {
							var id = children[i].id.split('__HIDDEN__');
							if (id.length == 2) {
								var element = appScope.getElementById(id[1]);
								element.options.value = JQuery('#value__HIDDEN__' + id[1]).val();
								JQuery('#' + element.id).get(0).setAttribute('value', element.options.value);
							}
						}

						var children = JQuery('#bfHiddenFieldsOptions').children('.bfHiddenOptionsTitle');
						for (var i = 0; i < children.length; i++) {
							var id = children[i].id.split('__HIDDEN__');
							if (id.length == 2) {
								var element = appScope.getElementById(id[1]);
								element.title = JQuery('#title__HIDDEN__' + id[1]).val();
							}
						}

						children = JQuery('#bfHiddenFieldsOptions').children('.bfHiddenOptionsName');
						for (var i = 0; i < children.length; i++) {
							var id = children[i].id.split('__HIDDEN__');
							if (id.length == 2) {
								var element = appScope.getElementById(id[1]);
								element.name = JQuery('#name__HIDDEN__' + id[1]).val();
							}
						}

						var children = JQuery('#bfHiddenFieldsOptions').children('.bfHiddenOptionsDelete');
						for (var i = 0; i < children.length; i++) {
							if (children[i].checked) {
								var id = children[i].id.split('__HIDDEN__');
								if (id.length == 2) {
									var element = appScope.getElementById(id[1]);
									JQuery('#' + id[1]).remove();
									appScope.removeElementFromAreaList(element);
								}
							}
						}

						appScope.populateHiddenFieldsOptions();

						JQuery('#bfHiddenFieldsOptions').get(0).innerHTML = '<?php echo BFText::_('COM_BREEZINGFORMS_OPTIONS_SAVED_TO_FINALLY_SAVE_YOUR_FORM_CLICK_SAVE_ON_THE_TOP_RIGHT_BUTTON') ?><br/><br/>' + JQuery('#bfHiddenFieldsOptions').get(0).innerHTML;
					}
				);

				JQuery('#bfHiddenFieldsOptions').append(element);
			}
		}

		/**
		* Return the elements of a droppableArea
		*/
		this.getElementsArray = function (area) {
			for (var i = 0; i < appScope.areaList.length; i++) {
				if (appScope.areaList[i].area && appScope.areaList[i].area == area) {
					return appScope.areaList[i].elements;
				}
			}

			return null;
		};

		/**
		* Checks if the droppableArea exists
		*/
		this.hasArea = function (area) {
			for (var i = 0; i < appScope.areaList.length; i++) {
				if (appScope.areaList[i].area && appScope.areaList[i].area == area) {
					return true;
				}
			}

			return false;
		};

		this.getElementById = function (id) {
			for (var i = 0; i < appScope.areaList.length; i++) {
				for (var j = 0; j < appScope.areaList[i].elements.length; j++) {
					if (id == appScope.areaList[i].elements[j].id) {
						return appScope.areaList[i].elements[j];
					}
				}
			}

			return null;
		};

		this.getElementsByType = function (type) {
			var foundElements = new Array();
			for (var i = 0; i < appScope.areaList.length; i++) {
				for (var j = 0; j < appScope.areaList[i].elements.length; j++) {
					if (type == appScope.areaList[i].elements[j].internalType) {
						foundElements.push(appScope.areaList[i].elements[j]);
					}
				}
			}

			return foundElements;
		};

		this.removeElementFromAreaList = function (element) {
			for (var i = 0; i < appScope.areaList.length; i++) {
				var newElements = new Array();
				for (var j = 0; j < appScope.areaList[i].elements.length; j++) {
					if (element.id != appScope.areaList[i].elements[j].id) {
						newElements.push(appScope.areaList[i].elements[j]);
					}
				}
				appScope.areaList[i].elements = newElements;
			}
		};

		this.removeElementFromTrashcanAreaList = function (element) {
			var newElements = new Array();
			for (var i = 0; i < appScope.trashcanAreaList.length; i++) {
				if (element.id != appScope.trashcanAreaList[i].id) {
					newElements.push(appScope.trashcanAreaList[i]);
				}
			}
			appScope.trashcanAreaList = newElements;
		};

		/**
		* prepares the data for the form before the submit
		*/
		this.prepareForSave = function () {

			if (appScope.captchaAdded > 1) {
				alert('<?php echo BFText::_('COM_BREEZINGFORMS_ADDED_MORE_THAN_ONE_CAPTCHA') ?>');
				return;
			}

			JQuery('.calCode').remove();
			JQuery('ui-resizable-handle').remove();

			var areas = new Array();

			for (var i = 0; i < appScope.areaList.length; i++) {
				areas.push({ id: appScope.areaList[i].area.id, elements: appScope.areaList[i].elements, pixelRaster: appScope.pixelRaster });

				for (var j = 0; j < appScope.areaList[i].elements.length; j++) {

					var element = appScope.areaList[i].elements[j];

					if ((element.internalType == 'bfTextfield' || element.internalType == 'bfSelect') && element.options && !element.options.mailback) { // compat 690
						element.options['mailback'] = false;
					}

					if (element.internalType == 'bfFile' && element.options && !element.options.allowedFileExtensions) { // compat 690
						element.options['allowedFileExtensions'] = '';
						element.data2 = '';
					} else {
						if (element.internalType == 'bfFile' && element.options) {
							element.data2 = element.options.allowedFileExtensions.toLowerCase();
						}
					}

					if (element.internalType == 'bfFile' && element.options && !element.options.attachToAdminMail) { // compat 691
						element.options['attachToAdminMail'] = false;
					}

					if (element.internalType == 'bfFile' && element.options && !element.options.attachToUserMail) { // compat 691
						element.options['attachToUserMail'] = false;
					}

					if (!element.mailbackfile) {
						element['mailbackfile'] = '';
					}

					if (!element.orderNumber) {
						element['orderNumber'] = -1;
					}

					if (!element.tabIndex) { // compat 687
						element['tabIndex'] = -1;
					}

					if (!element.mailbackAccept) { // compat 686
						element['mailbackAccept'] = false;
					}

					if (!element.mailbackAcceptConnectWith) { // compat 686
						element['mailbackAcceptConnectWith'] = '';
					}

					if (!element.mailbackAsSender) { // compat 687
						element['mailbackAsSender'] = false;
					}

					if (element.internalType == 'bfFile' && element.options.attachToAdminMail) {
						var attachToAdminMail = document.createElement('input');
						attachToAdminMail.setAttribute('type', 'hidden');
						attachToAdminMail.setAttribute('id', 'attachToAdminMail_' + element.name);
						attachToAdminMail.setAttribute('name', 'attachToAdminMail[' + element.name + ']');
						attachToAdminMail.setAttribute('class', 'attachToAdminMail');
						attachToAdminMail.setAttribute('value', 'true');
						JQuery('#bfTemplate').append(attachToAdminMail);
					}

					if (element.internalType == 'bfFile' && element.options.attachToUserMail) {
						var attachToUserMail = document.createElement('input');
						attachToUserMail.setAttribute('type', 'hidden');
						attachToUserMail.setAttribute('id', 'attachToUserMail_' + element.name);
						attachToUserMail.setAttribute('name', 'attachToUserMail[' + element.name + ']');
						attachToUserMail.setAttribute('class', 'attachToUserMail');
						attachToUserMail.setAttribute('value', 'true');
						JQuery('#bfTemplate').append(attachToUserMail);
					}

					if (element.internalType == 'bfTextfield' && element.mailbackAsSender) {
						var mailbackSender = document.createElement('input');
						mailbackSender.setAttribute('type', 'hidden');
						mailbackSender.setAttribute('id', 'mailbackSender_' + element.name);
						mailbackSender.setAttribute('name', 'mailbackSender[' + element.name + ']');
						mailbackSender.setAttribute('class', 'mailbackSender');
						mailbackSender.setAttribute('value', 'true');
						JQuery('#bfTemplate').append(mailbackSender);
					}

					if (element.internalType == 'bfCheckbox') {
						if (element.options.checked) {
							JQuery('#' + element.id).get(0).setAttribute('checked', 'checked');
						}
						if (element.mailbackAccept) {
							var connectWith = document.createElement('input');
							connectWith.setAttribute('type', 'hidden');
							connectWith.setAttribute('id', 'mailbackConnectWith_' + element.mailbackAcceptConnectWith);
							connectWith.setAttribute('name', 'mailbackConnectWith[' + element.mailbackAcceptConnectWith + ']');
							connectWith.setAttribute('class', 'mailbackConnectWith');
							connectWith.setAttribute('value', 'true_' + element.name);
							JQuery('#bfTemplate').append(connectWith);
						}
					}

					if (element.internalType == 'bfRadio') {
						if (element.options.checked) {
							JQuery('#' + element.id).get(0).setAttribute('checked', 'checked');
						}
					}

					if (element.internalType == 'bfSelect') {
						JQuery('#' + element.id).addClass('chzn-done');
					}

					if (element.internalType == 'bfCalendar' || element.internalType == 'bfCaptcha' || element.internalType == 'bfHidden' || element.internalType == 'bfStaticText' || element.internalType == 'bfIcon') {
						// leave out, only necessary for input elements
					} else {
						// move input elements out of their ui-wrappers
						JQuery('#' + element.id).parent().before(JQuery(JQuery('#' + element.id).parent().prev()).get(0)); // moving the label
						JQuery('#' + element.id).parent().before(JQuery('#' + element.id).get(0)); // moving the element itself
					}

					if (element.script2flag1 == 1) {
						var payment = "";
						if (element.internalType == 'bfPayPal') {
							payment = "document.getElementById('bfPaymentMethod').value='PayPal';";
						}
						else if (element.internalType == 'bfSofortueberweisung') {
							payment = "document.getElementById('bfPaymentMethod').value='Sofortueberweisung';";
						}
						JQuery('#' + element.id).get(0).setAttribute('onclick', payment + element.functionNameScript2 + '(this,\'click\');');
					}
					if (element.script2flag2 == 1) {
						JQuery('#' + element.id).get(0).setAttribute('onblur', element.functionNameScript2 + '(this,\'onblur\')');
					}
					if (element.script2flag3 == 1) {
						JQuery('#' + element.id).get(0).setAttribute('onchange', element.functionNameScript2 + '(this,\'onchange\')');
					}
					if (element.script2flag4 == 1) {
						JQuery('#' + element.id).get(0).setAttribute('onfocus', element.functionNameScript2 + '(this,\'onfocus\')');
					}
					if (element.script2flag5 == 1) {
						JQuery('#' + element.id).get(0).setAttribute('onselect', element.functionNameScript2 + '(this,\'onselect\')');
					}

					if (element.internalType == 'bfTooltip') {
						JQuery('#' + element.id).get(0).setAttribute('onmouseover', "return overlib('" + expstring(element.options.text) + "',CAPTION,'" + element.name + "',BELOW,RIGHT)");
						JQuery('#' + element.id).get(0).setAttribute('onmouseout', "return nd()");
					} else
						if (element.internalType == 'bfIcon') {
							JQuery(JQuery('#' + element.id).children('img')[0]).get(0).setAttribute('onmouseover', "this.src='" + element.data3 + "'");
							JQuery(JQuery('#' + element.id).children('img')[0]).get(0).setAttribute('onmouseout', "this.src='" + element.data1 + "'");
							JQuery('#' + element.id).get(0).setAttribute('onmouseover', "this.style.cursor = 'pointer'");
							JQuery('#' + element.id).get(0).setAttribute('onmouseout', "this.style.cursor = ''");
						} else
							if (element.internalType == 'bfCalendar') {

								var calCode = '<script class="calCode" type="text/javascript">if(typeof Calendar != "undefined"){Calendar.setup({inputField : ff_getElementByName("' + element.options.connectWith + '").id, ifFormat : "' + element.options.format + '", button : "' + element.id + '", align : "Bl", singleClick : true });}<\/script>';
								JQuery('.droppableArea').get(0).innerHTML += calCode;
							}
					if (JQuery('#' + element.id).parents('.bfPage')[0]) {
						var elPage = JQuery('#' + element.id).parents('.bfPage')[0].id.split('bfPage');
						element.page = parseInt(elPage[1]);
					} else {
						element.page = 1;
					}
					if (element.internalType != 'bfHidden' && parseInt(element.tabIndex) != -1) {
						JQuery('#' + element.id).get(0).setAttribute('tabindex', element.tabIndex);
					}
				}
			}

			JQuery('.ff_div').css('border', '');
			JQuery('.ff_elem').css('border', '');
			JQuery('.ff_label').css('border', '');
			JQuery('.ff_div').css('border-left', '');
			JQuery('.ff_elem').css('border-left', '');
			JQuery('.ff_label').css('border-left', '');
			JQuery('.ff_div').children('.ui-wrapper').remove();
			JQuery('.ff_div').children('.ui-resizable-handle').remove();
			JQuery('.ff_div').children('.ff_label').children('.ui-resizable-handle').remove();
			JQuery('.ff_div').children('.ff_elem').children('.ui-resizable-handle').remove();
			JQuery('.ff_label').removeClass('ui-resizable');
			JQuery('.ff_label').removeClass('ui-resizable-disabled');
			JQuery('.ff_label').removeClass('ui-state-disabled');
			JQuery('.ff_elem').removeClass('ui-resizable');
			JQuery('.ff_elem').removeClass('ui-resizable-disabled');
			JQuery('.ff_elem').removeClass('ui-state-disabled');
			JQuery('.droppableArea').removeClass('ui-sortable');
			JQuery('.droppableArea').removeClass('ui-droppable');
			JQuery('#bfTemplate').children('.ui-wrapper').remove();

			JQuery('.bfPage').css('display', 'none');
			JQuery('#bfPage1').css('display', '');

			var rep = JQuery('#bfTemplate').get(0).innerHTML.replace(/border-width: initial; /g, "");
			rep = rep.replace(/border-color: initial; /g, "");

			var base = 'base';
			var sixty_four = '64Encode';

			var result =
			{
				templateCode: JQuery[base + sixty_four](rep),
				areas: JQuery[base + sixty_four](JQuery.toJSON(areas))
			};

			return result;
		};

		this.createElementBeside = function (element, type) {

			var elements = appScope.getElementsArray(JQuery('#' + element.area).get(0));

			if (elements != null && type != '') {

				var ffListItem = null;

				if (JQuery('#' + element.id).parent().parent().parent().hasClass('ff_listItem')) {
					ffListItem = JQuery('#' + element.id).parent().parent().parent();
				} else if (JQuery('#' + element.id).parent().parent().hasClass('ff_listItem')) {
					ffListItem = JQuery('#' + element.id).parent().parent();
				} else {
					return;
				}

				JQuery(ffListItem).children('.ff_appender').remove();

				var info = appScope.getElementById(element.id);
				var rndId = JQuery.md5(Math.random() + info.appElementOrderId + info.appElementId + info.area);

				var besideElement = null;

				var wrapper = document.createElement('div');
				wrapper.setAttribute('id', 'ff_div' + rndId);
				wrapper.setAttribute('class', 'ff_div');

				if (type != 'bfStaticText') {

					var label = document.createElement('div');
					label.setAttribute('id', 'ff_elemLabel' + rndId);
					label.setAttribute('class', 'ff_label');

					label.style.verticalAlign = 'top';
					label.style.width = '50px';
					label.style.height = '10px';
					JQuery(label).css('float', 'left');
					label.innerHTML = 'Label...';

					JQuery(label).click(appScope.resizableElement);

					JQuery(wrapper).append(label);

					var mybr = document.createElement('div');
					mybr.setAttribute('id', 'ff_break' + rndId);
					mybr.setAttribute('class', 'ff_break');
					JQuery(mybr).css('display', 'none');

					JQuery(wrapper).append(mybr);
				}

				besideElement = appScope.createElementByType(type, rndId);

				if (besideElement != null) {

					JQuery(wrapper).append(besideElement.element);
					ffListItem.append(wrapper);

					var appender = document.createElement('div');
					appender.setAttribute('class', 'ff_appender');
					JQuery(appender).css('clear', 'both');
					JQuery(ffListItem).append(appender);

					elements.push(
						{
							id: besideElement.element.id,
							dbId: 0,
							rndId: rndId,
							name: rndId, // default name until changed by user
							title: 'title_' + besideElement.element.id, // default title until changed by user
							type: besideElement.element.type ? besideElement.element.type : '',
							internalType: type,
							bfType: besideElement.bfType,
							elementType: besideElement.elementType,
							area: element.area,
							appElementId: type,
							appElementOrderId: elements.length,
							wrapperId: wrapper.id,
							labelId: label ? label.id : '',
							listItemId: element.listItemId,
							data1: besideElement.data1,
							data2: besideElement.data2,
							data3: besideElement.data3,
							script1cond: besideElement.script1cond,
							script1id: besideElement.script1id,
							script1code: besideElement.script1code,
							script1flag1: besideElement.script1flag1,
							script1flag2: besideElement.script1flag2,
							script2cond: besideElement.script2cond,
							script2id: besideElement.script2id,
							script2code: besideElement.script2code,
							script2flag1: besideElement.script2flag1,
							script2flag2: besideElement.script2flag2,
							script2flag3: besideElement.script2flag3,
							script2flag4: besideElement.script2flag4,
							script2flag5: besideElement.script2flag5,
							script3cond: besideElement.script3cond,
							script3id: besideElement.script3id,
							script3code: besideElement.script3code,
							script3msg: besideElement.script3msg,
							functionNameScript1: besideElement.functionNameScript1,
							functionNameScript2: besideElement.functionNameScript2,
							functionNameScript3: besideElement.functionNameScript3,
							flag1: besideElement.flag1,
							flag2: besideElement.flag2,
							mailback: besideElement.mailback,
							mailbackfile: besideElement.mailbackfile,
							mailbackAsSender: false,
							mailbackAccept: false,
							mailbackAcceptConnectWith: '',
							orderNumber: -1,
							tabIndex: -1,
							page: parseInt(document.adminForm.page.value),
							options: besideElement.options
						}
					);

					if (type == 'bfCaptcha') {
						appScope.captchaAdded++;
					}

					appScope.populateHiddenFieldsOptions();
					if (type != 'bfHidden') {
						appScope.setElementResizable(besideElement.element);
					}
					var labelsResult = JQuery('#' + besideElement.element.id).parent().parent().children('.ff_label');
					if (labelsResult.length != 0) {
						for (var k = 0; k < labelsResult.length; k++) {
							appScope.setElementResizable(labelsResult[k]);
						}
					}
					labelsResult = JQuery('#' + besideElement.element.id).parent().children('.ff_label');
					if (labelsResult.length != 0) {
						for (var k = 0; k < labelsResult.length; k++) {
							appScope.setElementResizable(labelsResult[k]);
						}
					}
					appScope.initMouseOvers();
				}
			}

		};

		this.createElementBesideByType = function (element, selector) {
			this.createElementBeside(element, JQuery(selector).val());
		};

		this.disableElementsDetails = function () {
			JQuery('.bfOptionsWrapper').css('display', 'none');
			JQuery('.bfOptions').css('display', 'none');
			JQuery('.bfOptions').css('visibility', 'hidden');
			JQuery('#bfActions').css('display', 'none');
			JQuery('#bfActions').css('visibility', 'hidden');
			JQuery('#bfGlobalOptions').css('display', 'none');
			JQuery('#bfGlobalOptions').css('visibility', 'hidden');
			JQuery('#bfSaveOptionsButton').css('display', 'none');
			JQuery('#bfSaveOptionsButton').css('visibility', 'hidden');
			JQuery('#bfRemoveLabelButton').css('display', 'none');
			JQuery('#bfRemoveLabelButton').css('visibility', 'hidden');
			JQuery('#bfOptionsSaveMessage').css('display', 'none');
			JQuery('#bfOptionsSaveMessage').css('visibility', 'hidden');
			JQuery('#bfBesideCreationButton').css('display', 'none');
			JQuery('#bfBesideCreationButton').css('visibility', 'hidden');
			JQuery('#bfElementRemoveButton').css('display', 'none');
			JQuery('#bfElementRemoveButton').css('visibility', 'hidden');
			JQuery('#bfElementMoveLeft').css('display', 'none');
			JQuery('#bfElementMoveRight').css('display', 'none');
			JQuery('#bfInitScript').css('display', 'none');
			JQuery('#bfInitScript').css('visibility', 'hidden');
			JQuery('#bfActionScript').css('display', 'none');
			JQuery('#bfActionScript').css('visibility', 'hidden');
			JQuery('#bfValidationScript').css('display', 'none');
			JQuery('#bfValidationScript').css('visibility', 'hidden');
			JQuery('.bfScriptsSaveMessage').css('display', 'none');
		};

		this.handleLabel = function (label) {

			appScope.disableElementsDetails();
			appScope.optionElement = label;

			if (appScope.optionElement != null) {

				JQuery('#bfOptionsWrapper').css('display', '');
				JQuery('#bfGlobalOptions').css('display', '');
				JQuery('#bfGlobalOptions').css('visibility', 'visible');

				JQuery('#bfSaveOptionsButton').css('display', '');
				JQuery('#bfSaveOptionsButton').css('visibility', 'visible');

				JQuery('#bfRemoveLabelButton').css('display', '');
				JQuery('#bfRemoveLabelButton').css('visibility', 'visible');

				JQuery('#bfLabelOptions').css('display', '');
				JQuery('#bfLabelOptions').css('visibility', 'visible');

				JQuery(label).resizable('destroy');

				JQuery('#bfLabelContent').get(0).value = JQuery(label).get(0).innerHTML;

				appScope.setElementResizable(JQuery(label).get(0));

				if (JQuery(label).parent().children('.ff_break')[0].style.display != 'none') {
					JQuery('#bfLabelOnTop').attr('checked', true);
				} else {
					JQuery('#bfLabelOnTop').attr('checked', false);
				}

				JQuery('#bfLabelWidth').get(0).value = JQuery(label).get(0).style.width;
				JQuery('#bfLabelHeight').get(0).value = JQuery(label).get(0).style.height;
				JQuery('#bfOptionsPadding').get(0).value = JQuery(label).get(0).style.padding;
				JQuery('#bfOptionsMargin').get(0).value = JQuery(label).get(0).style.margin;
			}
		};

		this.populateElementValidationScript = function () {

			JQuery('#bfValidationScript').css('display', '');
			JQuery('#bfValidationScript').css('visibility', 'visible');

			JQuery('#bfValidationScriptSelection').empty();
			for (var i = 0; i < appScope.elementScripts.validation.length; i++) {
				var option = document.createElement('option');
				JQuery(option).val(appScope.elementScripts.validation[i].id);
				JQuery(option).text(appScope.elementScripts.validation[i].package + '::' + appScope.elementScripts.validation[i].name);
				if (appScope.elementScripts.validation[i].id == appScope.optionElement.script3id) {
					//JQuery(option).attr('selected', 'selected');
					JQuery(option).get(0).setAttribute('selected', 'selected');
				}
				JQuery('#bfValidationScriptSelection').append(option);
			}

			JQuery('#script3msg').val(appScope.optionElement.script3msg);
			JQuery('#script3code').val(appScope.optionElement.script3code);

			switch (appScope.optionElement.script3cond) {
				case 1:
					JQuery('.bfValidationType').attr('checked', '');
					JQuery('#bfValidationTypeLibrary').attr('checked', 'checked');
					JQuery('#bfValidationScriptLibrary').css('display', '');
					JQuery('#bfValidationScriptCustom').css('display', 'none');
					JQuery('#bfValidationScriptFlags').css('display', '');
					JQuery('#bfValidationScriptLibrary').css('display', '');
					JQuery('#bfValidationScriptCustom').css('display', 'none');
					break;
				case 2:
					JQuery('.bfValidationType').attr('checked', '');
					JQuery('#bfValidationTypeCustom').attr('checked', 'checked');
					JQuery('#bfValidationScriptFlags').css('display', '');
					JQuery('#bfValidationScriptLibrary').css('display', 'none');
					JQuery('#bfValidationScriptCustom').css('display', '');
					break;
				default:
					JQuery('.bfValidationType').attr('checked', '');
					JQuery('#bfValidationTypeNone').attr('checked', 'checked');
					JQuery('#bfValidationScriptFlags').css('display', 'none');
					JQuery('#bfValidationScriptLibrary').css('display', 'none');
					JQuery('#bfValidationScriptCustom').css('display', 'none');
			}
		};

		this.populateElementInitScript = function () {

			JQuery('#bfInitScript').css('display', '');
			JQuery('#bfInitScript').css('visibility', 'visible');

			JQuery('#bfInitScriptSelection').empty();
			for (var i = 0; i < appScope.elementScripts.init.length; i++) {
				var option = document.createElement('option');
				JQuery(option).val(appScope.elementScripts.init[i].id);
				JQuery(option).text(appScope.elementScripts.init[i].package + '::' + appScope.elementScripts.init[i].name);
				if (appScope.elementScripts.init[i].id == appScope.optionElement.script1id) {
					//JQuery(option).attr('selected', 'selected');
					JQuery(option).get(0).setAttribute('selected', 'selected');
				}
				JQuery('#bfInitScriptSelection').append(option);
			}

			if (appScope.optionElement.script1flag1 == 1) {
				JQuery('#script1flag1').get(0).checked = true;
			} else {
				JQuery('#script1flag1').get(0).checked = false;
			}

			if (appScope.optionElement.script1flag2 == 1) {
				JQuery('#script1flag2').get(0).checked = true;
			} else {
				JQuery('#script1flag2').get(0).checked = false;
			}

			JQuery('#script1code').val(appScope.optionElement.script1code);

			switch (appScope.optionElement.script1cond) {
				case 1:
					JQuery('.bfInitType').attr('checked', '');
					JQuery('#bfInitTypeLibrary').attr('checked', 'checked');
					JQuery('#bfInitScriptLibrary').css('display', '');
					JQuery('#bfInitScriptCustom').css('display', 'none');
					JQuery('#bfInitScriptFlags').css('display', '');
					JQuery('#bfInitScriptLibrary').css('display', '');
					JQuery('#bfInitScriptCustom').css('display', 'none');
					break;
				case 2:
					JQuery('.bfInitType').attr('checked', '');
					JQuery('#bfInitTypeCustom').attr('checked', 'checked');
					JQuery('#bfInitScriptFlags').css('display', '');
					JQuery('#bfInitScriptLibrary').css('display', 'none');
					JQuery('#bfInitScriptCustom').css('display', '');
					break;
				default:
					JQuery('.bfInitType').attr('checked', '');
					JQuery('#bfInitTypeNone').attr('checked', 'checked');
					JQuery('#bfInitScriptFlags').css('display', 'none');
					JQuery('#bfInitScriptLibrary').css('display', 'none');
					JQuery('#bfInitScriptCustom').css('display', 'none');
			}
		};

		this.populateElementActionScript = function () {

			JQuery('#bfActionScript').css('display', '');
			JQuery('#bfActionScript').css('visibility', 'visible');

			if (appScope.optionElement.internalType == 'bfSofortueberweisung' || appScope.optionElement.internalType == 'bfPayPal' || appScope.optionElement.internalType == 'bfIcon' || appScope.optionElement.internalType == 'bfImageButton' || appScope.optionElement.internalType == 'bfSubmitButton') {
				JQuery('.script2flag').css('display', 'none');
				JQuery('.script2flagLabel').css('display', 'none');
				JQuery('#script2flag1').css('display', '');
				JQuery('#script2flag1Label').css('display', '');
			} else {
				JQuery('.script2flag').css('display', '');
				JQuery('.script2flagLabel').css('display', '');
			}

			JQuery('#bfActionsScriptSelection').empty();

			for (var i = 0; i < appScope.elementScripts.action.length; i++) {

				var option = document.createElement('option');

				JQuery(option).val(appScope.elementScripts.action[i].id);
				JQuery(option).text(appScope.elementScripts.action[i].package + '::' + appScope.elementScripts.action[i].name);

				if (appScope.elementScripts.action[i].id == appScope.optionElement.script2id) {

					JQuery(option).get(0).setAttribute('selected', 'selected');
				}

				JQuery('#bfActionsScriptSelection').append(option);
			}

			if (appScope.optionElement.script2flag1 == 1) {
				JQuery('#script2flag1').get(0).checked = true;
			} else {
				JQuery('#script2flag1').get(0).checked = false;
			}

			if (appScope.optionElement.script2flag2 == 1) {
				JQuery('#script2flag2').get(0).checked = true;
			} else {
				JQuery('#script2flag2').get(0).checked = false;
			}

			if (appScope.optionElement.script2flag3 == 1) {
				JQuery('#script2flag3').get(0).checked = true;
			} else {
				JQuery('#script2flag3').get(0).checked = false;
			}

			if (appScope.optionElement.script2flag4 == 1) {
				JQuery('#script2flag4').get(0).checked = true;
			} else {
				JQuery('#script2flag4').get(0).checked = false;
			}

			if (appScope.optionElement.script2flag5 == 1) {
				JQuery('#script2flag5').get(0).checked = true;
			} else {
				JQuery('#script2flag5').get(0).checked = false;
			}

			JQuery('#script2code').val(appScope.optionElement.script2code);

			switch (appScope.optionElement.script2cond) {
				case 1:
					JQuery('.bfActionType').attr('checked', '');
					JQuery('#bfActionTypeLibrary').attr('checked', 'checked');
					JQuery('#bfActionScriptLibrary').css('display', '');
					JQuery('#bfActionScriptCustom').css('display', 'none');
					JQuery('#bfActionScriptFlags').css('display', '');
					JQuery('#bfActionScriptLibrary').css('display', '');
					JQuery('#bfActionScriptCustom').css('display', 'none');
					break;
				case 2:
					JQuery('.bfActionType').attr('checked', '');
					JQuery('#bfActionTypeCustom').attr('checked', 'checked');
					JQuery('#bfActionScriptFlags').css('display', '');
					JQuery('#bfActionScriptLibrary').css('display', 'none');
					JQuery('#bfActionScriptCustom').css('display', '');
					break;
				default:
					JQuery('.bfActionType').attr('checked', '');
					JQuery('#bfActionTypeNone').attr('checked', 'checked');
					JQuery('#bfActionScriptFlags').css('display', 'none');
					JQuery('#bfActionScriptLibrary').css('display', 'none');
					JQuery('#bfActionScriptCustom').css('display', 'none');
			}
		};

		/**
		* handles the selected element to show its options in the accordion menu
		*/
		this.handleElement = function (element) {

			appScope.disableElementsDetails();

			if (element != null) {

				appScope.optionElement = element;

				JQuery('#bfOptionsWrapper').css('display', '');
				JQuery('#bfActions').css('display', '');
				JQuery('#bfActions').css('visibility', 'visible');
				JQuery('#bfGlobalOptions').css('display', '');
				JQuery('#bfGlobalOptions').css('visibility', 'visible');
				JQuery('#bfSaveOptionsButton').css('display', '');
				JQuery('#bfSaveOptionsButton').css('visibility', 'visible');
				JQuery('#bfElementRemoveButton').css('display', '');
				JQuery('#bfElementRemoveButton').css('visibility', 'visible');
				JQuery('#bfElementMoveLeft').css('display', '');
				JQuery('#bfElementMoveLeft').css('visibility', 'visible');
				JQuery('#bfElementMoveRight').css('display', '');
				JQuery('#bfElementMoveRight').css('visibility', 'visible');
				JQuery('#bfBesideCreationButton').css('display', '');
				JQuery('#bfBesideCreationButton').css('visibility', 'visible');

				switch (element.internalType) {
					case 'bfStaticText':
						JQuery('#bfStaticTextOptions').css('display', '');
						JQuery('#bfStaticTextOptions').css('visibility', 'visible');
						JQuery('#bfStaticTextTitle').get(0).value = element.title;
						JQuery('#bfStaticTextWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfStaticTextHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						JQuery('#' + element.id).resizable('destroy');

						JQuery('#bfStaticTextContent').get(0).value = JQuery('#' + element.id).get(0).innerHTML;
						appScope.setElementResizable(JQuery('#' + element.id).get(0));
						break;
					case 'bfIcon':
						JQuery('#bfIconOptions').css('display', '');
						JQuery('#bfIconOptions').css('visibility', 'visible');
						JQuery('#bfIconCaption').get(0).value = JQuery('#' + element.id).children('#ff_iconCaption' + element.rndId)[0].innerHTML;
						JQuery('#bfIconWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfIconHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						JQuery('#bfIconImage').get(0).value = JQuery('#' + element.id).attr('src');
						JQuery('#bfIconImage').get(0).value = element.data1;
						JQuery('#bfIconImageOver').get(0).value = element.data3;
						appScope.populateElementActionScript();
						break;
					case 'bfCalendar':
						JQuery('#bfCalendarOptions').css('display', '');
						JQuery('#bfCalendarOptions').css('visibility', 'visible');
						JQuery('#bfCalendarFormat').get(0).value = element.options.format;
						JQuery('#bfCalendarConnectWith').get(0).value = element.options.connectWith;
						JQuery('#' + element.id).resizable('destroy');
						JQuery('#bfCalendarText').get(0).value = JQuery('#' + element.id).get(0).innerHTML.replace('', '');
						appScope.setElementResizable(JQuery('#' + element.id).get(0));
						break;
					case 'bfCaptcha':
						JQuery('#bfCaptchaOptions').css('display', '');
						JQuery('#bfCaptchaOptions').css('visibility', 'visible');
						JQuery('#bfCaptchaWidth').get(0).value = JQuery('.ff_captcha')[0].style.width;
						JQuery('#bfCaptchaHeight').get(0).value = JQuery('.ff_captcha')[0].style.height;
						break;
					case 'bfTextfield':
						JQuery('#bfTextfieldOptions').css('display', '');
						JQuery('#bfTextfieldOptions').css('visibility', 'visible');
						JQuery('#bfTextfieldTitle').get(0).value = element.title;
						JQuery('#bfTextfieldName').get(0).value = element.name;
						JQuery('#bfTextfieldValue').get(0).value = element.options.value;

						JQuery('#bfTextfieldWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfTextfieldHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						JQuery('#bfTextfieldMaxlength').get(0).value = JQuery('#' + element.id).attr('maxlength') > -1 ? JQuery('#' + element.id).attr('maxlength') : '';
						if (element.options.readonly) {
							JQuery('#bfTextfieldDisable').attr('checked', true);
							JQuery('#' + element.id).get(0).setAttribute('readonly', 'readonly');

						} else {
							JQuery('#bfTextfieldDisable').attr('checked', false);
							JQuery('#' + element.id).get(0).setAttribute('readonly', '');
							JQuery('#' + element.id).get(0).removeAttribute('readonly');
						}
						if (element.options.password) {
							JQuery('#bfTextfieldPassword').attr('checked', true);
							JQuery('#' + element.id).get(0).setAttribute('type', 'password');

						} else {
							JQuery('#bfTextfieldPassword').attr('checked', false);
							JQuery('#' + element.id).get(0).setAttribute('type', 'text');
						}
						if (element.options.mailback) {
							JQuery('#bfTextfieldMailback').attr('checked', true);

						} else {
							JQuery('#bfTextfieldMailback').attr('checked', false);
						}
						if (element.mailbackAsSender) {
							JQuery('#bfTextfieldMailbackAsSender').attr('checked', true);

						} else {
							JQuery('#bfTextfieldMailbackAsSender').attr('checked', false);
						}
						JQuery('#bfTextfieldMailbackfile').get(0).value = element.mailbackfile;
						appScope.populateElementActionScript();
						appScope.populateElementInitScript();
						appScope.populateElementValidationScript();
						break;
					case 'bfTextarea':
						JQuery('#bfTextareaOptions').css('display', '');
						JQuery('#bfTextareaOptions').css('visibility', 'visible');
						JQuery('#bfTextareaTitle').get(0).value = element.title;
						JQuery('#bfTextareaName').get(0).value = element.name;
						JQuery('#bfTextareaValue').get(0).value = element.options.value;
						JQuery('#bfTextareaWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfTextareaHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						if (element.options.readonly) {
							JQuery('#bfTextareaDisable').attr('checked', true);
							JQuery('#' + element.id).get(0).setAttribute('readonly', 'readonly');

						} else {
							JQuery('#bfTextareaDisable').attr('checked', false);
							JQuery('#' + element.id).get(0).setAttribute('readonly', '');
							JQuery('#' + element.id).get(0).removeAttribute('readonly');
						}
						appScope.populateElementInitScript();
						appScope.populateElementActionScript();
						appScope.populateElementValidationScript();
						break;
					case 'bfCheckbox':
						JQuery('#bfCheckboxOptions').css('display', '');
						JQuery('#bfCheckboxOptions').css('visibility', 'visible');
						JQuery('#bfCheckboxChecked').get(0).checked = element.options.checked;
						JQuery('#bfCheckboxValue').get(0).value = element.options.value;
						JQuery('#bfCheckboxValue').get(0).setAttribute('value', element.options.value);
						JQuery('#bfCheckboxTitle').get(0).value = element.title;
						JQuery('#bfCheckboxName').get(0).value = element.name;
						JQuery('#bfCheckboxWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfCheckboxHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						if (element.options.readonly) {
							JQuery('#bfCheckboxDisable').attr('checked', true);
							JQuery('#' + element.id).get(0).setAttribute('readonly', 'readonly');

						} else {
							JQuery('#bfCheckboxDisable').attr('checked', false);
							JQuery('#' + element.id).get(0).setAttribute('readonly', '');
							JQuery('#' + element.id).get(0).removeAttribute('readonly');
						}
						if (element.mailbackAccept) {
							JQuery('#bfCheckboxMailbackAccept').attr('checked', true);

						} else {
							JQuery('#bfCheckboxMailbackAccept').attr('checked', false);
						}
						JQuery('#bfCheckboxMailbackAcceptConnectWith').get(0).value = element.mailbackAcceptConnectWith;
						appScope.populateElementInitScript();
						appScope.populateElementActionScript();
						appScope.populateElementValidationScript();
						break;
					case 'bfRadio':
						JQuery('#bfRadioOptions').css('display', '');
						JQuery('#bfRadioOptions').css('visibility', 'visible');
						JQuery('#bfRadioChecked').get(0).checked = element.options.checked;
						JQuery('#bfRadioValue').get(0).value = element.options.value;
						JQuery('#bfRadioValue').get(0).setAttribute('value', element.options.value);
						JQuery('#bfRadioTitle').get(0).value = element.title;
						JQuery('#bfRadioName').get(0).value = element.name;
						JQuery('#bfRadioWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfRadioHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						if (element.options.readonly) {
							JQuery('#bfRadioDisable').attr('checked', true);
							JQuery('#' + element.id).get(0).setAttribute('readonly', 'readonly');

						} else {
							JQuery('#bfRadioDisable').attr('checked', false);
							JQuery('#' + element.id).get(0).setAttribute('readonly', '');
							JQuery('#' + element.id).get(0).removeAttribute('readonly');
						}
						appScope.populateElementInitScript();
						appScope.populateElementActionScript();
						appScope.populateElementValidationScript();
						break;
					case 'bfSelect':
						JQuery('#bfSelectOptions').css('display', '');
						JQuery('#bfSelectOptions').css('visibility', 'visible');
						JQuery('#bfSelectTitle').get(0).value = element.title;
						JQuery('#bfSelectName').get(0).value = element.name;

						if (element.options.multiple) {
							JQuery('#bfSelectMultipleYes').attr('checked', true);
							JQuery('#bfSelectMultipleNo').attr('checked', false);

						} else {
							JQuery('#bfSelectMultipleNo').attr('checked', true);
							JQuery('#bfSelectMultipleYes').attr('checked', false);
						}

						JQuery('#bfSelectOpts').get(0).value = element.options.options;

						JQuery('#bfSelectWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfSelectHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						if (element.options.readonly) {
							JQuery('#bfSelectDisable').attr('checked', true);
							JQuery('#' + element.id).get(0).setAttribute('readonly', 'readonly');

						} else {
							JQuery('#bfTextfieldDisable').attr('checked', false);
							JQuery('#' + element.id).get(0).setAttribute('readonly', '');
							JQuery('#' + element.id).get(0).removeAttribute('readonly');
						}
						if (element.options.mailback) {
							JQuery('#bfSelectMailback').attr('checked', true);

						} else {
							JQuery('#bfSelectMailback').attr('checked', false);
						}
						appScope.populateElementInitScript();
						appScope.populateElementActionScript();
						appScope.populateElementValidationScript();


						break;
					case 'bfFile':
						JQuery('#bfFileOptions').css('display', '');
						JQuery('#bfFileOptions').css('visibility', 'visible');
						JQuery('#bfFileTitle').get(0).value = element.title;
						JQuery('#bfFileName').get(0).value = element.name;

						if (element.options.timestamp) {
							JQuery('#bfFileTimestamp').attr('checked', true);
						} else {
							JQuery('#bfFileTimestamp').attr('checked', false);
						}

						if (element.options.attachToAdminMail) {
							JQuery('#bfFileAttachToAdminMail').attr('checked', true);
						} else {
							JQuery('#bfFileAttachToAdminMail').attr('checked', false);
						}

						if (element.options.attachToUserMail) {
							JQuery('#bfFileAttachToUserMail').attr('checked', true);
						} else {
							JQuery('#bfFileAttachToUserMail').attr('checked', false);
						}

						JQuery('#bfFileUploadDirectory').get(0).value = element.options.uploadDirectory;
						if (!element.options.allowedFileExtensions) element.options['allowedFileExtensions'] = -1; // compat 690
						JQuery('#bfFileAllowedFileExtensions').get(0).value = element.options.allowedFileExtensions;
						JQuery('#bfFileWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfFileHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						if (element.options.readonly) {
							JQuery('#bfFileDisable').attr('checked', true);
							JQuery('#' + element.id).get(0).setAttribute('readonly', 'readonly');

						} else {
							JQuery('#bfFileDisable').attr('checked', false);
							JQuery('#' + element.id).get(0).setAttribute('readonly', '');
							JQuery('#' + element.id).get(0).removeAttribute('readonly');
						}
						appScope.populateElementInitScript();
						appScope.populateElementActionScript();
						appScope.populateElementValidationScript();
						break;
					case 'bfImageButton':
						JQuery('#bfImageButtonOptions').css('display', '');
						JQuery('#bfImageButtonOptions').css('visibility', 'visible');
						JQuery('#bfImageButtonTitle').get(0).value = element.title;
						JQuery('#bfImageButtonName').get(0).value = element.name;
						JQuery('#bfImageButtonValue').get(0).value = element.options.value;
						JQuery('#bfImageButtonWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfImageButtonHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						JQuery('#bfImageButtonImage').get(0).value = JQuery('#' + element.id).get(0).getAttribute('src');
						if (element.options.readonly) {
							JQuery('#bfImageButtonDisable').attr('checked', true);
							JQuery('#' + element.id).get(0).setAttribute('readonly', 'readonly');

						} else {
							JQuery('#bfImageButtonDisable').attr('checked', false);
							JQuery('#' + element.id).get(0).setAttribute('readonly', '');
							JQuery('#' + element.id).get(0).removeAttribute('readonly');
						}
						appScope.populateElementActionScript();
						break;
					case 'bfSubmitButton':
						JQuery('#bfSubmitButtonOptions').css('display', '');
						JQuery('#bfSubmitButtonOptions').css('visibility', 'visible');
						JQuery('#bfSubmitButtonTitle').get(0).value = element.title;
						JQuery('#bfSubmitButtonName').get(0).value = element.name;
						JQuery('#bfSubmitButtonValue').get(0).value = element.options.value;
						JQuery('#bfSubmitButtonWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfSubmitButtonHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						if (element.options.readonly) {
							JQuery('#bfSubmitButtonDisable').attr('checked', true);
							JQuery('#' + element.id).get(0).setAttribute('readonly', 'readonly');

						} else {
							JQuery('#bfSubmitButtonDisable').attr('checked', false);
							JQuery('#' + element.id).get(0).setAttribute('readonly', '');
							JQuery('#' + element.id).get(0).removeAttribute('readonly');
						}
						appScope.populateElementActionScript();
						break;
					case 'bfTooltip':
						JQuery('#bfTooltipOptions').css('display', '');
						JQuery('#bfTooltipOptions').css('visibility', 'visible');
						JQuery('#bfTooltipTitle').get(0).value = element.title;
						JQuery('#bfTooltipName').get(0).value = element.name;
						if (element.options.type == 'info') {
							JQuery('#bfTooltipTypeInfo').get(0).checked = true;
							JQuery('#bfTooltipTypeWarning').get(0).checked = false;
							JQuery('#bfTooltipTypeCustom').get(0).checked = false;
						} else if (element.options.type == 'warning') {
							JQuery('#bfTooltipTypeInfo').get(0).checked = false;
							JQuery('#bfTooltipTypeWarning').get(0).checked = true;
							JQuery('#bfTooltipTypeCustom').get(0).checked = false;
						} else if (element.options.type == 'custom') {
							JQuery('#bfTooltipTypeInfo').get(0).checked = false;
							JQuery('#bfTooltipTypeWarning').get(0).checked = false;
							JQuery('#bfTooltipTypeCustom').get(0).checked = true;
							JQuery('#bfTooltipCustomImage').get(0).value = element.options.image;
						}
						JQuery('#bfTooltipText').get(0).value = element.options.text;
						JQuery('#bfTooltipWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfTooltipHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;
						break;

					case 'bfPayPal':

						JQuery('#bfPayPalOptions').css('display', '');
						JQuery('#bfPayPalOptions').css('visibility', 'visible');

						JQuery('#bfPayPalTitle').get(0).value = element.title;
						JQuery('#bfPayPalName').get(0).value = element.name;

						if (element.options.testaccount) {
							JQuery('#bfPayPalTestaccountYes').attr('checked', 'checked');
							JQuery('#bfPayPalTestaccountNo').attr('checked', '');
						} else {
							JQuery('#bfPayPalTestaccountYes').attr('checked', '');
							JQuery('#bfPayPalTestaccountNo').attr('checked', 'checked');
						}

						if (element.options.downloadableFile) {
							JQuery('#bfPayPalDownloadableFileYes').attr('checked', 'checked');
							JQuery('#bfPayPalDownloadableFileNo').attr('checked', '');
						} else {
							JQuery('#bfPayPalDownloadableFileYes').attr('checked', '');
							JQuery('#bfPayPalDownloadableFileNo').attr('checked', 'checked');
						}

						if (typeof element.options.useIpn == "undefined") { // compat 730
							element.options['useIpn'] = false;
						}

						if (element.options.useIpn) {
							JQuery('#bfPayPalUseIpnYes').attr('checked', 'checked');
							JQuery('#bfPayPalUseIpnNo').attr('checked', '');
						} else {
							JQuery('#bfPayPalUseIpnYes').attr('checked', '');
							JQuery('#bfPayPalUseIpnNo').attr('checked', 'checked');
						}

						JQuery('#bfPayPalFilepath').get(0).value = element.options.filepath;
						JQuery('#bfPayPalFileDownloadTries').get(0).value = element.options.downloadTries;
						JQuery('#bfPayPalBusiness').get(0).value = element.options.business;
						JQuery('#bfPayPalToken').get(0).value = element.options.token;
						JQuery('#bfPayPalTestBusiness').get(0).value = element.options.testBusiness;
						JQuery('#bfPayPalTestToken').get(0).value = element.options.testToken;
						JQuery('#bfPayPalItemname').get(0).value = element.options.itemname;
						JQuery('#bfPayPalItemnumber').get(0).value = element.options.itemnumber;
						JQuery('#bfPayPalAmount').get(0).value = element.options.amount;
						JQuery('#bfPayPalTax').get(0).value = element.options.tax;
						JQuery('#bfPayPalThankYouPage').get(0).value = element.options.thankYouPage;
						JQuery('#bfPayPalLocale').get(0).value = element.options.locale;
						JQuery('#bfPayPalCurrencyCode').get(0).value = element.options.currencyCode;
						JQuery('#bfPayPalImage').get(0).value = JQuery('#' + element.id).attr('src');
						JQuery('#bfPayPalWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfPayPalHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;

						appScope.populateElementActionScript();

						break;

					case 'bfSofortueberweisung':

						JQuery('#bfSofortueberweisungOptions').css('display', '');
						JQuery('#bfSofortueberweisungOptions').css('visibility', 'visible');

						JQuery('#bfSofortueberweisungTitle').get(0).value = element.title;
						JQuery('#bfSofortueberweisungName').get(0).value = element.name;

						if (element.options.downloadableFile) {
							JQuery('#bfSofortueberweisungDownloadableFileYes').attr('checked', 'checked');
							JQuery('#bfSofortueberweisungDownloadableFileNo').attr('checked', '');
						} else {
							JQuery('#bfSofortueberweisungDownloadableFileYes').attr('checked', '');
							JQuery('#bfSofortueberweisungDownloadableFileNo').attr('checked', 'checked');
						}

						if (element.options.mailback) {
							JQuery('#bfSofortueberweisungMailbackYes').attr('checked', 'checked');
							JQuery('#bfSofortueberweisungMailbackNo').attr('checked', '');
						} else {
							JQuery('#bfSofortueberweisungMailbackYes').attr('checked', '');
							JQuery('#bfSofortueberweisungMailbackNo').attr('checked', 'checked');
						}

						JQuery('#bfSofortueberweisungFilepath').get(0).value = element.options.filepath;
						JQuery('#bfSofortueberweisungFileDownloadTries').get(0).value = element.options.downloadTries;
						JQuery('#bfSofortueberweisungUserId').get(0).value = element.options.user_id;
						JQuery('#bfSofortueberweisungProjectId').get(0).value = element.options.project_id;
						JQuery('#bfSofortueberweisungProjectPassword').get(0).value = element.options.project_password;
						JQuery('#bfSofortueberweisungReason1').get(0).value = element.options.reason_1;
						JQuery('#bfSofortueberweisungReason2').get(0).value = element.options.reason_2;
						JQuery('#bfSofortueberweisungAmount').get(0).value = element.options.amount;
						JQuery('#bfSofortueberweisungThankYouPage').get(0).value = element.options.thankYouPage;
						JQuery('#bfSofortueberweisungLanguageId').get(0).value = element.options.language_id;
						JQuery('#bfSofortueberweisungCurrencyId').get(0).value = element.options.currency_id;
						JQuery('#bfSofortueberweisungImage').get(0).value = JQuery('#' + element.id).attr('src');
						JQuery('#bfSofortueberweisungWidth').get(0).value = JQuery('#' + element.id).get(0).style.width;
						JQuery('#bfSofortueberweisungHeight').get(0).value = JQuery('#' + element.id).get(0).style.height;

						appScope.populateElementActionScript();

						break;
				}


				JQuery('#bfOptionsPadding').get(0).value = JQuery('#' + element.id).get(0).style.padding;
				JQuery('#bfOptionsMargin').get(0).value = JQuery('#' + element.id).get(0).style.margin;
				JQuery('#bfOptionsOrder').get(0).value = element.orderNumber;
				JQuery('#bfOptionsTabIndex').get(0).value = element.tabIndex;
			}
		};

		this.resizableElement = function () {

			var internalElement = null;
			var clazz = JQuery(this).attr('class');

			if (clazz.substr(0, 8) != 'ff_label') {
				if (clazz == 'ui-resizable-handle ui-resizable-se' || clazz == 'ui-resizable-handle ui-resizable-se ') {
					internalElement = appScope.getElementById(JQuery(JQuery(this).parent().children('.ff_elem')[0]).attr('id'));
				} else {
					internalElement = appScope.getElementById(JQuery(this).attr('id'));
				}

				appScope.handleElement(internalElement);
			} else {
				internalElement = this;
				appScope.handleLabel(internalElement);
			}
		};

		this.cleanResizables = function () {
			JQuery('.ff_elem').resizable('disable');
			JQuery('.ff_label').resizable('disable');
		}

		this.destroyResizables = function () {
			JQuery('.ff_elem').resizable('disable');
			JQuery('.ff_label').resizable('disable');
			JQuery('.ff_elem').resizable('destroy');
			JQuery('.ff_label').resizable('destroy');
		}

		this.removeElement = function (element) {
			JQuery('.bfOptionsWrapper').css('display', 'none');
			JQuery('.bfOptions').css('display', 'none');
			JQuery('.bfOptions').css('visibility', 'hidden');
			JQuery('#bfActions').css('display', 'none');
			JQuery('#bfActions').css('visibility', 'hidden');
			JQuery('#bfSaveOptionsButton').css('display', 'none');
			JQuery('#bfSaveOptionsButton').css('visibility', 'hidden');
			JQuery('#bfOptionsSaveMessage').css('display', 'none');
			JQuery('#bfOptionsSaveMessage').css('visibility', 'hidden');
			JQuery('#bfBesideCreationButton').css('display', 'none');
			JQuery('#bfBesideCreationButton').css('visibility', 'hidden');
			JQuery('#bfElementRemoveButton').css('display', 'none');
			JQuery('#bfElementRemoveButton').css('visibility', 'hidden');
			JQuery('#bfInitScript').css('display', 'none');
			JQuery('#bfInitScript').css('visibility', 'hidden');
			JQuery('#bfActionScript').css('display', 'none');
			JQuery('#bfActionScript').css('visibility', 'hidden');
			JQuery('#bfValidationScript').css('display', 'none');
			JQuery('#bfValidationScript').css('visibility', 'hidden');
			JQuery('.bfScriptsSaveMessage').css('display', 'none');
			JQuery('#bfElementMoveLeft').css('display', 'none');
			JQuery('#bfElementMoveRight').css('display', 'none');

			JQuery('#' + element.wrapperId).remove();
			if (element.internalType == 'bfCaptcha') {
				appScope.captchaAdded--;
			}
			if (JQuery('#' + element.listItemId).children('.ff_div').length == 0) {
				JQuery('#' + element.listItemId).remove();
			}
			appScope.optionElement = null;
			appScope.removeElementFromAreaList(element);
		};

		this.moveElement = function (element, direction) {
			if (direction != 'prev' && direction != 'next') {
				return;
			}

			if (direction == 'next') {
				if (typeof JQuery('#' + element.wrapperId + ' + .ff_div') != 'undefined') {
					var nextElement = JQuery('#' + element.wrapperId + ' + .ff_div');
					JQuery('#' + element.wrapperId).insertAfter(nextElement);
				}
				if (typeof JQuery('#' + element.wrapperId + ' + .ui-wrapper') != 'undefined') {
					var nextElement = JQuery('#' + element.wrapperId + ' + .ui-wrapper');
					JQuery('#' + element.wrapperId).insertAfter(nextElement);
				}
			} else if (direction == 'prev') {

				if (typeof JQuery('#' + element.wrapperId).prev() != 'undefined' && JQuery('#' + element.wrapperId).prev().attr('class') == 'ff_div') {
					var prevElement = JQuery('#' + element.wrapperId).prev();
					JQuery('#' + element.wrapperId).insertBefore(prevElement);
				}
			}
		}

		this.createElementByType = function (typeName, rndId) {

			var element = null;
			var bfType = '';
			var elementType = '';
			var options = {};
			var data1 = '';
			var data2 = '';
			var data3 = '';
			var script1cond = 0;
			var script1id = 0;
			var script1code = '';
			var script1flag1 = 0;
			var script1flag2 = 0;
			var script2cond = 0;
			var script2id = 0;
			var script2code = '';
			var script2flag1 = 0;
			var script2flag2 = 0;
			var script2flag3 = 0;
			var script2flag4 = 0;
			var script2flag5 = 0;
			var script3cond = 0;
			var script3id = 0;
			var script3code = '';
			var script3msg = '';
			var functionNameScript1 = '';
			var functionNameScript2 = '';
			var functionNameScript3 = '';
			var flag1 = 0;
			var flag2 = 0;
			var mailback = 0;
			var mailbackfile = '';

			switch (typeName) {

				case 'bfCalendar':

					bfType = 'Calendar';
					elementType = 'div';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('class', 'ff_elem');
					element.innerHTML = 'pick date';

					JQuery(element).click(appScope.resizableElement);

					options = { format: '%Y-%m-%d', connectWith: '' };

					break;

				case 'bfCaptcha':

					bfType = 'Captcha';
					elementType = 'div';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('class', 'ff_elem');

					var imageHolder = document.createElement('img');
					imageHolder.setAttribute('id', 'ff_capimgValue');
					imageHolder.setAttribute('class', 'ff_capimg');
					imageHolder.setAttribute('src', 'components/com_breezingforms/images/captcha/securimage_show.php');

					elementType = 'br';
					var br = document.createElement(elementType);

					elementType = 'input';
					var cap = document.createElement(elementType);
					cap.setAttribute('id', 'bfCaptchaEntry');
					cap.setAttribute('name', 'bfCaptchaEntry');
					cap.setAttribute('type', 'text');
					cap.setAttribute('class', 'ff_captcha');
					JQuery(cap).css('width', '200px');
					JQuery(cap).css('float', 'left');
					var reload = document.createElement('div');
					JQuery(reload).css('float', 'left');
					JQuery(reload).css('padding-left', '5px');
					reload.innerHTML = '<a href="#" onclick="document.getElementById(\'bfCaptchaEntry\').value=\'\';document.getElementById(\'bfCaptchaEntry\').focus();var splitsrc = document.getElementById(\'ff_capimgValue\').src.split(\'?\'); document.getElementById(\'ff_capimgValue\').src = splitsrc[0] + \'?bfMathRandom=\' + Math.random(); return false"><img src="<?php echo Uri::root() ?>components/com_breezingforms/images/captcha/refresh-captcha.png" border="0" /></a>';

					JQuery(element).append(imageHolder);
					JQuery(element).append(br);
					JQuery(element).append(cap);
					JQuery(element).append(reload);

					JQuery(element).click(appScope.resizableElement);

					break;

				case 'bfIcon':

					var iconImage = '<?php echo Uri::root() ?>components/com_breezingforms/images/next.png';

					bfType = 'Icon';
					elementType = 'div';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('class', 'ff_elem');
					element.setAttribute('align', 'center');

					var imageHolder = document.createElement('img');
					imageHolder.setAttribute('id', 'ff_icon' + rndId);
					imageHolder.setAttribute('class', 'ff_icon');
					imageHolder.setAttribute('src', iconImage);

					var textHolder = document.createElement('div');
					textHolder.setAttribute('id', 'ff_iconCaption' + rndId);
					textHolder.setAttribute('class', 'ff_iconCaption');
					textHolder.innerHTML = '<div align="center">Next</div>';

					JQuery(element).append(imageHolder);
					JQuery(element).append(textHolder);

					JQuery(element).click(appScope.resizableElement);

					data3 = '<?php echo Uri::root() ?>components/com_breezingforms/images/next_f2.png',
						data1 = iconImage;

					break;

				case 'bfTextfield':

					bfType = 'Text';
					elementType = 'input';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'text');
					element.setAttribute('class', 'ff_elem');
					JQuery(element).focus(appScope.resizableElement);
					JQuery(element).css('width', '170px');
					JQuery(element).css('height', '18px');

					options = { value: '', readonly: false, password: false, mailback: false };

					break;

				case 'bfStaticText':

					bfType = 'Static Text/HTML';
					elementType = 'div';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('class', 'ff_elem');
					element.innerHTML = 'Enter Text here...';
					element.style.width = '150px';
					element.style.height = '20px';
					JQuery(element).click(appScope.resizableElement);

					break;

				case 'bfTextarea':

					bfType = 'Textarea';
					elementType = 'textarea';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'textarea');
					element.setAttribute('class', 'ff_elem');

					JQuery(element).focus(appScope.resizableElement);

					options = { value: '', readonly: false };

					break;

				case 'bfCheckbox':

					bfType = 'Checkbox';
					elementType = 'input';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'checkbox');
					element.setAttribute('class', 'ff_elem');
					JQuery(element).click(appScope.resizableElement);

					options = { checked: false, value: 'cb', readonly: false };
					data1 = 'cb';
					break;

				case 'bfRadio':

					bfType = 'Radio Button';
					elementType = 'input';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'radio');
					element.setAttribute('class', 'ff_elem');
					JQuery(element).click(appScope.resizableElement);

					options = { checked: false, value: 'on', readonly: false };
					data1 = 'on';
					break;


				case 'bfSelect':

					bfType = 'Select List';
					elementType = 'select';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('class', 'ff_elem');
					JQuery(element).focus(appScope.resizableElement);
					JQuery(element).css('width', '110px');
					JQuery(element).css('height', '25px');
					JQuery(element).addClass('chzn-done');

					options = { multiple: false, options: "1;Select Color;\n0;Red;red\n0;Green;green\n0;Blue;blue", readonly: false, mailback: false };

					data1 = 1;
					data2 = "1;Select Color;\n0;Red;red\n0;Green;green\n0;Blue;blue";

					break;

				case 'bfFile':

					bfType = 'File Upload';
					elementType = 'input';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'file');
					element.setAttribute('class', 'ff_elem');

					JQuery(element).css('height', '25px');

					JQuery(element).click(appScope.resizableElement);

					options = {
						uploadDirectory: '{ff_uploads}',
						timestamp: false,
						readonly: false,
						allowedFileExtensions: 'zip,rar,pdf,doc,xls,ppt,jpg,jpeg,gif,png',
						attachToUserMail: false,
						attachToAdminMail: false
					};

					data1 = '{ff_uploads}';
					break;

				case 'bfSubmitButton':

					bfType = 'Regular Button';
					elementType = 'input';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'submit');
					element.setAttribute('class', 'ff_elem');
					JQuery(element).click(appScope.resizableElement);
					JQuery(element).css('padding', '0');
					JQuery(element).css('width', '176px');
					JQuery(element).css('height', '26px');

					options = { readonly: false, value: '' };

					break;

				case 'bfImageButton':

					var iconImage = '<?php echo Uri::root() ?>components/com_breezingforms/images/next.png';
					bfType = 'Graphic Button';
					elementType = 'input';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'image');
					element.setAttribute('class', 'ff_elem');
					element.setAttribute('src', iconImage);
					data1 = iconImage;
					JQuery(element).click(appScope.resizableElement);

					options = { readonly: false, value: '' };

					break;

				case 'bfHidden':

					bfType = 'Hidden Input';
					elementType = 'input';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'hidden');
					element.setAttribute('class', 'ff_elem');

					options = { value: '' };

					break;

				case 'bfTooltip':

					bfType = 'Tooltip';
					elementType = 'img';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('class', 'ff_elem');
					element.setAttribute('src', '<?php echo Uri::root() ?>/components/com_breezingforms/images/tooltip.png');
					JQuery(element).click(appScope.resizableElement);

					options = { text: 'Some <em>hint</em> or <strong/>warning</strong> for the user', image: '<?php echo Uri::root() ?>/components/com_breezingforms/images/tooltip.png', type: 'info' };
					data2 = 'Some <em>hint</em> or <strong/>warning</strong> for the user';
					break;

				case 'bfPayPal':

					var iconImage = 'http://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif';
					bfType = 'PayPal';
					elementType = 'input';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'image');
					element.setAttribute('class', 'ff_elem');
					element.setAttribute('src', iconImage);
					JQuery(element).css('width', '144px');
					JQuery(element).css('height', '47px');
					data1 = iconImage;
					JQuery(element).click(appScope.resizableElement);

					options = {
						testaccount: false,
						downloadableFile: false,
						filepath: '',
						downloadTries: 1,
						business: '',
						token: '',
						useIpn: false,
						testBusiness: '',
						testToken: '',
						itemname: '',
						itemnumber: '',
						amount: '',
						tax: '',
						thankYouPage: '<?php echo Uri::root() ?>',
						locale: 'us',
						currencyCode: 'USD',
						image: iconImage
					};

					break;

				case 'bfSofortueberweisung':

					var iconImage = '<?php echo Uri::root() ?>components/com_breezingforms/images/200x65px.png';
					bfType = 'Sofortueberweisung';
					elementType = 'input';
					element = document.createElement(elementType);
					element.setAttribute('id', 'ff_elem' + rndId);
					element.setAttribute('name', 'ff_nm_' + rndId + '[]');
					element.setAttribute('type', 'image');
					element.setAttribute('class', 'ff_elem');
					element.setAttribute('src', iconImage);
					JQuery(element).css('width', '144px');
					JQuery(element).css('height', '47px');
					data1 = iconImage;
					JQuery(element).click(appScope.resizableElement);

					options = {
						downloadableFile: false,
						filepath: '',
						downloadTries: 1,
						user_id: '',
						project_id: '',
						project_password: '',
						reason_1: '',
						reason_2: '',
						amount: '',
						thankYouPage: '<?php echo Uri::root() ?>',
						language_id: 'DE',
						currency_id: 'EUR',
						image: iconImage,
						mailback: false
					};

					break;

			}


			JQuery(element).css('border', 'none');

			return {
				element: element,
				bfType: bfType,
				elementType: elementType,
				data1: data1,
				data2: data2,
				data3: data3,
				script1cond: script1cond,
				script1id: script1id,
				script1code: script1code,
				script1flag1: script1flag1,
				script1flag2: script1flag2,
				script2cond: script2cond,
				script2id: script2id,
				script2code: script2code,
				script2flag1: script2flag1,
				script2flag2: script2flag2,
				script2flag3: script2flag3,
				script2flag4: script2flag4,
				script2flag5: script2flag5,
				script3cond: script3cond,
				script3id: script3id,
				script3code: script3code,
				script3msg: script3msg,
				functionNameScript1: functionNameScript1,
				functionNameScript2: functionNameScript2,
				functionNameScript3: functionNameScript3,
				flag1: flag1,
				flag2: flag2,
				mailback: mailback,
				mailbackfile: mailbackfile,
				options: options
			};

		};

		this.initMouseOvers = function () {


			JQuery('.ff_div').mouseover(function () {
				JQuery('.ff_div').css('border', 'none');
				JQuery(this).css('border', '1px dotted #000');

				JQuery('.ff_div').children('.ff_label').children('.ui-resizable-se').css('display', 'none');
				JQuery('.ff_div').children('.ui-wrapper').children('.ui-resizable-se').css('display', 'none');
				JQuery('.ff_div').children('.ff_elem').children('.ui-resizable-se').css('display', 'none');

				if (JQuery(this).children('.ff_label').length != 0) {
					var label = JQuery(JQuery(this).children('.ff_label')[0]);
					var labres = JQuery(label.children('.ui-resizable-se')[0]);
					labres.css('display', '');
				}

				if (JQuery(this).children('.ui-wrapper').length != 0) {
					var wrapper = JQuery(JQuery(this).children('.ui-wrapper')[0]);
					var res = JQuery(wrapper.children('.ui-resizable-se')[0]);
					res.css('display', 'block');

					wrapper.css('padding-bottom', '-13px');

					if (wrapper.children('.ff_elem').length != 0) {
						var elem = JQuery(wrapper.children('.ff_elem')[0]);
						//elem.css('padding','0');
						elem.css('border', 'none');
					}

				} else if (JQuery(this).children('.ff_elem').length != 0) {
					var elem = JQuery(JQuery(this).children('.ff_elem')[0]);
					elem.children('.ui-resizable-se').css('display', '');
					elem.css('border', 'none');
				}
			});

			JQuery('.ff_div').bind('mouseleave', function () {

				JQuery(this).css('border', 'none');

				if (JQuery(this).children('.ff_label').length != 0) {
					var label = JQuery(JQuery(this).children('.ff_label')[0]);
					var labres = JQuery(label.children('.ui-resizable-se')[0]);
					labres.css('display', 'none');
				}

				if (JQuery(this).children('.ui-wrapper').length != 0) {
					var wrapper = JQuery(JQuery(this).children('.ui-wrapper')[0]);
					var res = JQuery(wrapper.children('.ui-resizable-se')[0]);
					res.css('display', 'none');

					wrapper.css('padding-bottom', '0px');

					if (wrapper.children('.ff_elem').length != 0) {
						var elem = JQuery(wrapper.children('.ff_elem')[0]);
						//elem.css('padding','0');
						elem.css('border', 'none');
					}
				} else if (JQuery(this).children('.ff_elem').length != 0) {
					var elem = JQuery(JQuery(this).children('.ff_elem')[0]);
					elem.children('.ui-resizable-se').css('display', 'none');
					elem.css('border', 'none');
				}

			});

			JQuery('.ui-wrapper').mouseover(function () {
				JQuery(this).css('border', '1px dotted #000');
			});

			JQuery('.ui-wrapper').mouseout(function () {
				JQuery(this).css('border', 'none');
			});

			JQuery('.ff_label').mouseover(function () {
				JQuery(this).css('border', '1px dotted #000');
			});

			JQuery('.ff_label').mouseout(function () {
				JQuery(this).css('border', 'none');
			});

		};

		this.refreshBatchOptions = function () {
			JQuery('#bfBatchLabels').empty();
			JQuery('#bfBatchElements').empty();
			for (var i = 0; i < appScope.areaList.length; i++) {

				for (var j = 0; j < appScope.areaList[i].elements.length; j++) {
					var element = appScope.areaList[i].elements[j]

					var labelsResult = JQuery('#' + element.id).parent().parent().children('.ff_label');
					if (labelsResult.length != 0) {
						for (var k = 0; k < labelsResult.length; k++) {
							var batchLabel = document.createElement('option');
							JQuery(batchLabel).val(labelsResult[k].id);
							JQuery(batchLabel).text(stripHTML(JQuery('#' + labelsResult[k].id).html()));
							JQuery('#bfBatchLabels').append(batchLabel);
						}
					}

					labelsResult = JQuery('#' + element.id).parent().children('.ff_label');
					if (labelsResult.length != 0) {
						for (var k = 0; k < labelsResult.length; k++) {
							var batchLabel = document.createElement('option');
							JQuery(batchLabel).val(labelsResult[k].id);
							JQuery(batchLabel).text(stripHTML(JQuery('#' + labelsResult[k].id).html()));
							JQuery('#bfBatchLabels').append(batchLabel);
						}
					}

					if (element.internalType != 'bfHidden') {
						var batchElement = document.createElement('option');
						JQuery(batchElement).val(element.id);
						JQuery(batchElement).text(element.name);
						JQuery('#bfBatchElements').append(batchElement);
					}
				}
			}
		};

		/**
		* JQuery initalizations
		*/
		appScope.initMouseOvers();

		JQuery('.attachToAdminMail').remove();
		JQuery('.attachToUserMail').remove();
		JQuery('.mailbackConnectWith').remove();
		JQuery('.mailbackSender').remove();

		JQuery('#bfUpdateTemplateButton').click(function () {
			JQuery('#bfTemplate').get(0).innerHTML = JQuery.trim(JQuery('#bfTemplateBox').val());
			submitbutton('save');
		});

		JQuery('.ff_elem').css('border', 'none');
		JQuery('.ff_div').children('.ff_label').children('.ui-resizable-se').css('display', 'none');
		JQuery('.ff_div').children('.ui-wrapper').children('.ui-resizable-se').css('display', 'none');
		JQuery('.ff_div').children('.ff_elem').children('.ui-resizable-se').css('display', 'none');
		JQuery('.ff_div').children('.ui-wrapper').css('padding-bottom', '0px');

		JQuery('.ff_elem').click(this.resizableElement);
		JQuery('.ff_elem').focus(this.resizableElement);
		JQuery('.ui-resizable-se').click(this.resizableElement);
		JQuery('.ff_label').click(this.resizableElement);

		JQuery("#menutab").tabs({ select: function (e, ui) { } });
		JQuery("#nestedaccordion").accordion({
			autoHeight: false, collapsible: true, change: function (e, ui) {
				appScope.refreshTemplateBox();
			}
		});
		JQuery("#nestedaccordion2").accordion({
			autoHeight: false, collapsible: true, change: function (e, ui) {
				appScope.refreshTemplateBox();
			}
		});
		JQuery("#nestedaccordion3").accordion({
			autoHeight: false, collapsible: true, change: function (e, ui) {
				appScope.refreshTemplateBox();
			}
		});

		JQuery('#bfBatchButton').click(function () {

			var width = JQuery.trim(JQuery('#bfBatchWidth').val());
			var height = JQuery.trim(JQuery('#bfBatchHeight').val());
			var padding = JQuery.trim(JQuery('#bfBatchPadding').val());
			var margin = JQuery.trim(JQuery('#bfBatchMargin').val());

			JQuery('#bfBatchLabels :selected').each(function (i, selected) {
				var label = JQuery('#' + JQuery(selected).val());
				if (width != '') {
					label.css('width', width);
				}
				if (height != '') {
					label.css('height', height);
				}
				if (padding != '') {
					label.css('padding', padding);
				}
				if (margin != '') {
					label.css('margin', margin);
				}
			});
			JQuery('#bfBatchElements :selected').each(function (i, selected) {

				var element = JQuery('#' + JQuery(selected).val());

				if (width != '') {
					element.css('width', width);
				}
				if (height != '') {
					element.css('height', height);
				}
				if (padding != '') {
					element.css('padding', padding);
				}
				if (margin != '') {
					element.css('margin', margin);
				}

				if (JQuery('#' + JQuery(selected).val()).parent().attr('class') == 'ui-wrapper') {

					element = JQuery('#' + JQuery(selected).val()).parent();

					if (width != '') {
						element.css('width', width);
					}
					if (height != '') {
						element.css('height', height);
					}
					if (padding != '') {
						element.css('padding', padding);
					}
					if (margin != '') {
						element.css('margin', margin);
					}
				}
			});
		});

		JQuery('#bfUpdatePixelRaster').click(
			function () {
				appScope.pixelRaster = parseInt(JQuery('#bfPixelRaster').val());
				submitbutton('save');
			}
		);

		JQuery('#bfGoToPage').change(
			function () {
				if (parseInt(JQuery(this).val()) > 0) {
					document.adminForm.page.value = JQuery(this).val();
					submitbutton('save');
				}
			}
		);

		JQuery('#bfMoveThisPageTo').change(
			function () {

				if (parseInt(JQuery(this).val()) > 0) {

					if (parseInt(JQuery(this).val()) == 1) {
						JQuery('#bfPage' + JQuery(this).val()).before(JQuery('#bfPage' + document.adminForm.page.value).get(0));
					} else {
						JQuery('#bfPage' + JQuery(this).val()).after(JQuery('#bfPage' + document.adminForm.page.value).get(0));
					}

					var children = JQuery('#bfTemplate').children('.bfPage');
					var clength = children.length;
					for (var i = 0; i < clength; i++) {
						JQuery(children[i]).get(0).setAttribute('id', 'bfPage' + (i + 1));
					}

					var idbag = {};
					for (var i = 0; i < JQuery('#bfTemplate').children('.bfPage').length; i++) {
						var page = JQuery('#bfTemplate').children('.bfPage')[i];
						for (var j = 0; j < JQuery(page).find('.ff_elem').length; j++) {
							idbag[JQuery(page).find('.ff_elem')[j].id] = (i + 1);
						}
					}

					for (var i = 0; i < appScope.areaList.length; i++) {
						for (var j = 0; j < appScope.areaList[i].elements.length; j++) {
							if (idbag[appScope.areaList[i].elements[j].id]) {
								appScope.areaList[i].elements[j].page = idbag[appScope.areaList[i].elements[j].id];
								break;
							}
						}
					}

					document.adminForm.page.value = parseInt(JQuery(this).val());
					submitbutton('save');
				};
			}
		);

		JQuery('#bfDeleteThisPage').click(
			function () {

				if (!confirm('<?php echo BFText::_('COM_BREEZINGFORMS_ARE_YOU_SURE_TO_DELETE_THIS_PAGE') ?>')) {
					return;
				}

				var areaToRemove = null;
				for (var i = 0; i < appScope.areaList.length; i++) {
					for (var j = 0; j < appScope.areaList[i].elements.length; j++) {
						if (appScope.areaList[i].elements[j].page == document.adminForm.page.value) {
							areaToRemove = appScope.areaList[i];
							break;
						}
					}
				}

				if (areaToRemove != null) {
					var newAreaList = new Array();
					for (var i = 0; i < appScope.areaList.length; i++) {
						if (areaToRemove.area.id != appScope.areaList[i].area.id) {
							newAreaList.push(appScope.areaList[i]);
						}
					}
					appScope.areaList = newAreaList;
				}

				JQuery('#bfPage' + document.adminForm.page.value).remove();

				var children = JQuery('#bfTemplate').children('.bfPage');
				var clength = children.length;
				for (var i = 0; i < clength; i++) {
					JQuery(children[i]).get(0).setAttribute('id', 'bfPage' + (i + 1));
				}

				var idbag = {};
				for (var i = 0; i < JQuery('#bfTemplate').children('.bfPage').length; i++) {
					var page = JQuery('#bfTemplate').children('.bfPage')[i];
					for (var j = 0; j < JQuery(page).find('.ff_elem').length; j++) {
						idbag[JQuery(page).find('.ff_elem')[j].id] = (i + 1);
					}
				}

				for (var i = 0; i < appScope.areaList.length; i++) {
					for (var j = 0; j < appScope.areaList[i].elements.length; j++) {
						if (idbag[appScope.areaList[i].elements[j].id]) {
							appScope.areaList[i].elements[j].page = idbag[appScope.areaList[i].elements[j].id];
							break;
						}
					}
				}

				document.adminForm.page.value = 1;
				document.adminForm.pages.value = parseInt(document.adminForm.pages.value) - 1;
				submitbutton('save');
			}
		);

		JQuery('#bfCreatePage').click(
			function () {

				if (JQuery('#bfTemplate').children('.bfPage').length == 0) {

					var page = document.createElement('span');
					page.setAttribute('class', 'bfPage');
					page.setAttribute('id', 'bfPage1');

					if (JQuery('#bfTemplate').children('*').length == 0) {
						var area = document.createElement('ul');
						area.setAttribute('class', 'droppableArea');
						area.setAttribute('id', 'drop1');
						JQuery(page).append(area);
						JQuery('#bfTemplate').append(page);
						appScope.areaList.push({ area: JQuery(area).get(0), elements: [] });
						document.adminForm.pages.value = 1;
						submitbutton('save');
					} else {
						var children = JQuery('#bfTemplate').children('*');
						var tlength = children.length;
						for (var i = 0; i < tlength; i++) {
							JQuery(page).append(children[i]);
						}
						JQuery('#bfTemplate').append(page);

						document.adminForm.pages.value = 1;
					}

				} else {
					//rearranging the page numbers
					var children = JQuery('#bfTemplate').children('.bfPage');
					var clength = children.length;
					for (var i = 0; i < clength; i++) {
						JQuery(children[i]).get(0).setAttribute('id', 'bfPage' + (i + 1));
					}

					var page = document.createElement('span');
					page.setAttribute('class', 'bfPage');
					page.setAttribute('id', 'bfPage' + (clength + 1));
					var area = document.createElement('ul');
					appScope.areaList.push({ area: JQuery(area).get(0), elements: [] });
					area.setAttribute('class', 'droppableArea');

					if (!JQuery('#' + 'drop' + (JQuery('.droppableArea').length + 1)).get(0)) {
						area.setAttribute('id', 'drop' + (JQuery('.droppableArea').length + 1));
					} else {
						// trying random drop name if the id already exists
						var rndId = JQuery.md5("rnd" + Math.random() + (clength + 1));
						area.setAttribute('id', 'drop' + rndId);
					}

					JQuery(page).append(area);
					JQuery('#bfTemplate').append(page);

					document.adminForm.pages.value = clength + 1;
					submitbutton('save');
				}
			}
		);

		JQuery('#bfRemoveLabelButton').click(
			function () {
				JQuery(JQuery('#' + appScope.optionElement.id).parent().children('.ff_break')[0]).remove();
				JQuery('#' + appScope.optionElement.id).remove();
				appScope.disableElementsDetails();
			}
		);

		JQuery('#bfInitButton').click(
			function () {

				if (JQuery('#script1flag1').get(0).checked) {
					appScope.optionElement.script1flag1 = 1;
				} else {
					appScope.optionElement.script1flag1 = 0;
				}

				if (JQuery('#script1flag2').get(0).checked) {
					appScope.optionElement.script1flag2 = 1;
				} else {
					appScope.optionElement.script1flag2 = 0;
				}

				appScope.optionElement.script1id = JQuery('#bfInitScriptSelection').val();
				appScope.optionElement.script1code = JQuery('#script1code').val();

				if (JQuery('#bfInitTypeLibrary').get(0).checked) {
					appScope.optionElement.script1cond = 1;
					for (var i = 0; i < appScope.elementScripts.init.length; i++) {
						if (appScope.elementScripts.init[i].id == JQuery('#bfInitScriptSelection').val()) {
							appScope.optionElement.functionNameScript1 = appScope.elementScripts.init[i].name;
							break;
						}
					}
				} else if (JQuery('#bfInitTypeCustom').get(0).checked) {
					appScope.optionElement.script1cond = 2;
					appScope.optionElement.functionNameScript1 = 'ff_' + appScope.optionElement.name + '_init';
				} else {
					appScope.optionElement.script1cond = 0;
				}

				JQuery('.bfScriptsSaveMessage').css('display', '');
				JQuery('.bfScriptsSaveMessage').empty().append('<?php echo BFText::_('COM_BREEZINGFORMS_SCRIPT_SAVED_TO_FINALLY_SAVE_YOUR_FORM_CLICK_SAVE_ON_THE_TOP_RIGHT_BUTTON') ?><br/><br/>');
			}
		);

		JQuery('#bfActionButton').click(
			function () {

				if (JQuery('#script2flag1').get(0).checked) {
					appScope.optionElement.script2flag1 = 1;
				} else {
					appScope.optionElement.script2flag1 = 0;
				}

				if (JQuery('#script2flag2').get(0).checked) {
					appScope.optionElement.script2flag2 = 1;
				} else {
					appScope.optionElement.script2flag2 = 0;
				}

				if (JQuery('#script2flag3').get(0).checked) {
					appScope.optionElement.script2flag3 = 1;
				} else {
					appScope.optionElement.script2flag3 = 0;
				}

				if (JQuery('#script2flag4').get(0).checked) {
					appScope.optionElement.script2flag4 = 1;
				} else {
					appScope.optionElement.script2flag4 = 0;
				}

				if (JQuery('#script2flag5').get(0).checked) {
					appScope.optionElement.script2flag5 = 1;
				} else {
					appScope.optionElement.script2flag5 = 0;
				}

				appScope.optionElement.script2id = JQuery('#bfActionsScriptSelection').val();
				appScope.optionElement.script2code = JQuery('#script2code').val();

				if (JQuery('#bfActionTypeLibrary').get(0).checked) {
					appScope.optionElement.script2cond = 1;
					for (var i = 0; i < appScope.elementScripts.action.length; i++) {
						if (appScope.elementScripts.action[i].id == JQuery('#bfActionsScriptSelection').val()) {
							appScope.optionElement.functionNameScript2 = appScope.elementScripts.action[i].name;
							break;
						}
					}
				} else if (JQuery('#bfActionTypeCustom').get(0).checked) {
					appScope.optionElement.script2cond = 2;
					appScope.optionElement.functionNameScript2 = 'ff_' + appScope.optionElement.name + '_action';
				} else {
					appScope.optionElement.script2cond = 0;
				}

				JQuery('.bfScriptsSaveMessage').css('display', '');
				JQuery('.bfScriptsSaveMessage').empty().append('<?php echo BFText::_('COM_BREEZINGFORMS_SCRIPT_SAVED_TO_FINALLY_SAVE_YOUR_FORM_CLICK_SAVE_ON_THE_TOP_RIGHT_BUTTON') ?><br/><br/>');
			}
		);

		JQuery('#bfValidationButton').click(
			function () {

				appScope.optionElement.script3id = JQuery('#bfValidationScriptSelection').val();
				appScope.optionElement.script3code = JQuery('#script3code').val();
				appScope.optionElement.script3msg = JQuery('#script3msg').val();

				if (JQuery('#bfValidationTypeLibrary').get(0).checked) {
					appScope.optionElement.script3cond = 1;
					for (var i = 0; i < appScope.elementScripts.validation.length; i++) {
						if (appScope.elementScripts.validation[i].id == JQuery('#bfValidationScriptSelection').val()) {
							appScope.optionElement.functionNameScript3 = appScope.elementScripts.validation[i].name;
							break;
						}
					}
				} else if (JQuery('#bfValidationTypeCustom').get(0).checked) {
					appScope.optionElement.script3cond = 2;
					appScope.optionElement.functionNameScript3 = 'ff_' + appScope.optionElement.name + '_validation';
				} else {
					appScope.optionElement.script3cond = 0;
				}

				JQuery('.bfScriptsSaveMessage').css('display', '');
				JQuery('.bfScriptsSaveMessage').empty().append('<?php echo BFText::_('COM_BREEZINGFORMS_SCRIPT_SAVED_TO_FINALLY_SAVE_YOUR_FORM_CLICK_SAVE_ON_THE_TOP_RIGHT_BUTTON') ?><br/><br/>');
			}
		);

		JQuery('#bfSaveOptionsButton').click(
			function () {
				var error = '';

				if (appScope.optionElement.internalType) {

					switch (appScope.optionElement.internalType) {
						case 'bfCalendar':
							appScope.optionElement.options.format = JQuery.trim(JQuery('#bfCalendarFormat').get(0).value);
							appScope.optionElement.options.connectWith = JQuery.trim(JQuery('#bfCalendarConnectWith').get(0).value);
							JQuery('#' + appScope.optionElement.id).resizable('destroy');
							JQuery('#' + appScope.optionElement.id).get(0).innerHTML = JQuery.trim(JQuery('#bfCalendarText').get(0).value);
							appScope.setElementResizable(JQuery('#' + appScope.optionElement.id).get(0));
							break;
						case 'bfCaptcha':
							JQuery('.ff_captcha')[0].style.width = JQuery.trim(JQuery('#bfCaptchaWidth').get(0).value);
							JQuery('.ff_captcha')[0].style.height = JQuery.trim(JQuery('#bfCaptchaHeight').get(0).value);
							break;
						case 'bfStaticText':
							if (JQuery.trim(JQuery('#bfStaticTextTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfStaticTextContent').get(0).value) == '') {
								error = 'Content must not be empty!';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfStaticTextTitle').get(0).value);
								JQuery('#' + appScope.optionElement.id).resizable('destroy');
								JQuery('#' + appScope.optionElement.id).get(0).innerHTML = JQuery.trim(JQuery('#bfStaticTextContent').get(0).value);
								appScope.setElementResizable(JQuery('#' + appScope.optionElement.id).get(0));
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfStaticTextWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfStaticTextHeight').get(0).value);
							}
							break;
						case 'bfIcon':
							if (JQuery.trim(JQuery('#bfIconCaption').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_CAPTION_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfIconImage').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_ICON_IMAGE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfIconImageOver').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_ICON_IMAGE_OVER_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfIconCaption').get(0).value);
								JQuery('#' + appScope.optionElement.id).children('.ff_iconCaption')[0].innerHTML = JQuery.trim(JQuery('#bfIconCaption').get(0).value);

								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfIconWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfIconHeight').get(0).value);
								JQuery('#' + appScope.optionElement.id).children('.ff_icon')[0].setAttribute('src', JQuery.trim(JQuery('#bfIconImage').get(0).value));
							}
							break;
						case 'bfTextfield':
							if (JQuery.trim(JQuery('#bfTextfieldTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfTextfieldName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfTextfieldTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfTextfieldName').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).setAttribute('value', JQuery.trim(JQuery('#bfTextfieldValue').get(0).value));
								JQuery('#' + appScope.optionElement.id).get(0).value = JQuery.trim(JQuery('#bfTextfieldValue').get(0).value);
								appScope.optionElement.options.value = JQuery.trim(JQuery('#bfTextfieldValue').get(0).value);

								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfTextfieldWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfTextfieldHeight').get(0).value);

								if (JQuery.trim(JQuery('#bfTextfieldMaxlength').get(0).value) == '') {
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('maxlength');
								} else {
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('maxlength', JQuery.trim(JQuery('#bfTextfieldMaxlength').get(0).value));
								}
								if (JQuery('#bfTextfieldDisable').get(0).checked) {
									appScope.optionElement.flag2 = 1;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', 'readonly');
									appScope.optionElement.options.readonly = true;

								} else {
									appScope.optionElement.flag2 = 0;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', '');
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('readonly');
									appScope.optionElement.options.readonly = false;
								}
								if (JQuery('#bfTextfieldPassword').get(0).checked) {
									appScope.optionElement.flag1 = 1;
									appScope.optionElement.options.password = true;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('type', 'password');

								} else {
									appScope.optionElement.options.password = false;
									appScope.optionElement.flag1 = 0;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('type', 'text');
								}
								if (JQuery('#bfTextfieldMailback').get(0).checked) {
									appScope.optionElement.mailback = 1;
									appScope.optionElement.options.mailback = true;
								} else {
									appScope.optionElement.options.mailback = false;
									appScope.optionElement.mailback = 0;
								}
								if (JQuery('#bfTextfieldMailbackAsSender').get(0).checked) {
									appScope.optionElement.mailbackAsSender = true;
								} else {
									appScope.optionElement.mailbackAsSender = false;
								}
								appScope.optionElement.mailbackfile = JQuery('#bfTextfieldMailbackfile').get(0).value;
							}
							break;
						case 'bfTextarea':
							if (JQuery.trim(JQuery('#bfTextareaTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfTextareaName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfTextareaTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfTextareaName').get(0).value);
								appScope.optionElement.options.value = JQuery.trim(JQuery('#bfTextareaValue').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).value = JQuery.trim(JQuery('#bfTextareaValue').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).defaultValue = JQuery.trim(JQuery('#bfTextareaValue').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfTextareaWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfTextareaHeight').get(0).value);
								if (JQuery('#bfTextareaDisable').get(0).checked) {
									appScope.optionElement.flag2 = 1;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', 'readonly');
									appScope.optionElement.options.readonly = true;

								} else {
									appScope.optionElement.flag2 = 0;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', '');
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('readonly');
									appScope.optionElement.options.readonly = false;
								}
							}
							break;
						case 'bfCheckbox':
							if (JQuery.trim(JQuery('#bfCheckboxTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfCheckboxName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfCheckboxTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfCheckboxName').get(0).value);
								appScope.optionElement.options.checked = JQuery('#bfCheckboxChecked').get(0).checked;
								JQuery('#' + appScope.optionElement.id).get(0).checked = JQuery('#bfCheckboxChecked').get(0).checked;
								if (JQuery('#bfCheckboxChecked').get(0).checked) {
									appScope.optionElement.options.checked = true;
									appScope.optionElement.flag1 = 1;
									JQuery('#' + appScope.optionElement.id).attr('checked', true);
								} else {
									appScope.optionElement.flag1 = 0;
									JQuery('#' + appScope.optionElement.id).attr('checked', false);
									appScope.optionElement.options.checked = false;
								}
								appScope.optionElement.options.value = JQuery.trim(JQuery('#bfCheckboxValue').get(0).value);
								appScope.optionElement.data1 = JQuery.trim(JQuery('#bfCheckboxValue').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).value = JQuery.trim(JQuery('#bfCheckboxValue').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfCheckboxWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfCheckboxHeight').get(0).value);
								if (JQuery('#bfCheckboxDisable').get(0).checked) {
									appScope.optionElement.flag2 = 1;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', 'readonly');
									appScope.optionElement.options.readonly = true;

								} else {
									appScope.optionElement.flag2 = 0;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', '');
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('readonly');
									appScope.optionElement.options.readonly = false;
								}
								if (JQuery('#bfCheckboxMailbackAccept').get(0).checked) {
									appScope.optionElement.mailbackAccept = true;
								} else {
									appScope.optionElement.mailbackAccept = false;
								}

								appScope.optionElement.mailbackAcceptConnectWith = JQuery('#bfCheckboxMailbackAcceptConnectWith').val();
							}
							break;
						case 'bfRadio':
							if (JQuery.trim(JQuery('#bfRadioTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfRadioName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfRadioTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfRadioName').get(0).value);
								appScope.optionElement.options.checked = JQuery('#bfRadioChecked').get(0).checked;
								JQuery('#' + appScope.optionElement.id).get(0).checked = JQuery('#bfRadioChecked').get(0).checked;
								if (JQuery('#bfRadioChecked').get(0).checked) {
									appScope.optionElement.flag1 = 1;
									JQuery('#' + appScope.optionElement.id).attr('checked', true);
								} else {
									appScope.optionElement.flag1 = 0;
									JQuery('#' + appScope.optionElement.id).attr('checked', false);
								}
								appScope.optionElement.options.value = JQuery.trim(JQuery('#bfRadioValue').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).value = JQuery.trim(JQuery('#bfRadioValue').get(0).value);
								appScope.optionElement.data1 = JQuery.trim(JQuery('#bfRadioValue').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfRadioWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfRadioHeight').get(0).value);
								if (JQuery('#bfRadioDisable').get(0).checked) {
									appScope.optionElement.flag2 = 1;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', 'readonly');
									appScope.optionElement.options.readonly = true;

								} else {
									appScope.optionElement.flag2 = 0;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', '');
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('readonly');
									appScope.optionElement.options.readonly = false;
								}
							}
							break;
						case 'bfSelect':
							if (JQuery.trim(JQuery('#bfSelectTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfSelectName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfSelectTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfSelectName').get(0).value);

								if (JQuery('#bfSelectMultipleYes').get(0).checked) {
									appScope.optionElement.flag1 = 1;
									appScope.optionElement.options.multiple = true;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('multiple', 'multiple');
								} else {
									appScope.optionElement.options.multiple = false;
									appScope.optionElement.flag1 = 0;
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('multiple');
								}

								JQuery('#' + appScope.optionElement.id).empty();

								var raw = JQuery.trim(JQuery('#bfSelectOpts').get(0).value);
								raw = raw.replace(/\r/g, "");
								appScope.optionElement.data2 = raw;

								var lines = raw.split("\n");

								for (var i = 0; i < lines.length; i++) {

									var line = lines[i].split(';');
									if (line.length == 3) {
										var option = document.createElement('option');
										option.setAttribute('value', JQuery.trim(line[2]));
										option.innerHTML = JQuery.trim(line[1]);
										if (JQuery.trim(line[0]) == '1') {
											option.setAttribute('selected', 'selected');
										}
									}

									JQuery('#' + appScope.optionElement.id).append(option);
								}

								appScope.optionElement.options.options = JQuery.trim(JQuery('#bfSelectOpts').get(0).value);

								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfSelectWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfSelectHeight').get(0).value);

								if (JQuery('#bfSelectDisable').get(0).checked) {
									appScope.optionElement.flag2 = 1;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', 'readonly');
									appScope.optionElement.options.readonly = true;

								} else {
									appScope.optionElement.flag2 = 0;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', '');
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('readonly');
									appScope.optionElement.options.readonly = false;
								}
								if (JQuery('#bfSelectMailback').get(0).checked) {
									appScope.optionElement.mailback = 1;
									appScope.optionElement.options.mailback = true;
								} else {
									appScope.optionElement.options.mailback = false;
									appScope.optionElement.mailback = 0;
								}
							}
							break;
						case 'bfFile':
							if (JQuery.trim(JQuery('#bfFileTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfFileName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfFileTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfFileName').get(0).value);
								if (!appScope.optionElement.options.allowedFileExtensions) appScope.optionElement.options['allowedFileExtensions'] = -1;
								appScope.optionElement.options.allowedFileExtensions = JQuery.trim(JQuery('#bfFileAllowedFileExtensions').get(0).value);
								appScope.optionElement.options.uploadDirectory = JQuery.trim(JQuery('#bfFileUploadDirectory').get(0).value);
								appScope.optionElement.data1 = JQuery.trim(JQuery('#bfFileUploadDirectory').get(0).value);
								appScope.optionElement.options.timestamp = JQuery('#bfFileTimestamp').get(0).checked;
								if (JQuery('#bfFileTimestamp').get(0).checked) {
									appScope.optionElement.flag1 = 1;
									JQuery('#' + appScope.optionElement.id).attr('checked', true);
								} else {
									appScope.optionElement.flag1 = 0;
									JQuery('#' + appScope.optionElement.id).attr('checked', false);
								}
								if (!appScope.optionElement.options.attachToAdminMail) appScope.optionElement.options['attachToAdminMail'] = false;
								if (JQuery('#bfFileAttachToAdminMail').get(0).checked) {
									appScope.optionElement.options.attachToAdminMail = true;
								} else {
									appScope.optionElement.options.attachToAdminMail = false;
								}
								if (!appScope.optionElement.options.attachToUserMail) appScope.optionElement.options['attachToUserMail'] = false;
								if (JQuery('#bfFileAttachToUserMail').get(0).checked) {
									appScope.optionElement.options.attachToUserMail = true;
								} else {
									appScope.optionElement.options.attachToUserMail = false;
								}
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfFileWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfFileHeight').get(0).value);
								if (JQuery('#bfFileDisable').get(0).checked) {
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', 'readonly');
									appScope.optionElement.options.readonly = true;

								} else {
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', '');
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('readonly');
									appScope.optionElement.options.readonly = false;
								}
							}
							break;
						case 'bfImageButton':
							if (JQuery.trim(JQuery('#bfImageButtonTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfImageButtonName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfImageButtonTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfImageButtonName').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfImageButtonWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfImageButtonHeight').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).setAttribute('src', JQuery.trim(JQuery('#bfImageButtonImage').get(0).value));
								JQuery('#' + appScope.optionElement.id).get(0).setAttribute('value', JQuery.trim(JQuery('#bfImageButtonValue').get(0).value));
								JQuery('#' + appScope.optionElement.id).get(0).value = JQuery.trim(JQuery('#bfImageButtonValue').get(0).value);
								appScope.optionElement.options.value = JQuery.trim(JQuery('#bfImageButtonValue').get(0).value);
								if (JQuery('#bfImageButtonDisable').get(0).checked) {
									appScope.optionElement.flag2 = 1;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', 'readonly');
									appScope.optionElement.options.readonly = true;

								} else {
									appScope.optionElement.flag2 = 0;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', '');
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('readonly');
									appScope.optionElement.options.readonly = false;
								}
							}
							break;
						case 'bfSubmitButton':
							if (JQuery.trim(JQuery('#bfSubmitButtonTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfSubmitButtonName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfSubmitButtonTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfSubmitButtonName').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfSubmitButtonWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfSubmitButtonHeight').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).setAttribute('value', JQuery.trim(JQuery('#bfSubmitButtonValue').get(0).value));
								JQuery('#' + appScope.optionElement.id).get(0).value = JQuery.trim(JQuery('#bfSubmitButtonValue').get(0).value);
								appScope.optionElement.options.value = JQuery.trim(JQuery('#bfSubmitButtonValue').get(0).value);
								if (JQuery('#bfSubmitButtonDisable').get(0).checked) {
									appScope.optionElement.flag2 = 1;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', 'readonly');
									appScope.optionElement.options.readonly = true;

								} else {
									appScope.optionElement.flag2 = 0;
									JQuery('#' + appScope.optionElement.id).get(0).setAttribute('readonly', '');
									JQuery('#' + appScope.optionElement.id).get(0).removeAttribute('readonly');
									appScope.optionElement.options.readonly = false;
								}
							}
							break;
						case 'bfTooltip':
							if (JQuery.trim(JQuery('#bfTooltipTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfTooltipName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}
							if (error == '') {
								appScope.optionElement.title = JQuery.trim(JQuery('#bfTooltipTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfTooltipName').get(0).value);
								if (JQuery('#bfTooltipTypeInfo').get(0).checked) {
									appScope.optionElement.flag1 = 0;
									appScope.optionElement.options.image = '<?php echo Uri::root(); ?>includes/js/ThemeOffice/tooltip.png';
									appScope.optionElement.options.type = 'info';
								} else if (JQuery('#bfTooltipTypeWarning').get(0).checked) {
									appScope.optionElement.flag1 = 1;
									appScope.optionElement.options.image = '<?php echo Uri::root(); ?>includes/js/ThemeOffice/warning.png';
									appScope.optionElement.options.type = 'warning';
								} else if (JQuery('#bfTooltipTypeCustom').get(0).checked) {
									appScope.optionElement.options.image = JQuery.trim(JQuery('#bfTooltipCustomImage').get(0).value);
									appScope.optionElement.options.type = 'custom';
								}
								JQuery('#' + appScope.optionElement.id).get(0).setAttribute('src', appScope.optionElement.options.image);
								appScope.optionElement.options.text = JQuery.trim(JQuery('#bfTooltipText').get(0).value);
								appScope.optionElement.data2 = JQuery.trim(JQuery('#bfTooltipText').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfTooltipWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfTooltipHeight').get(0).value);

							}
							break;

						case 'bfPayPal':

							if (JQuery.trim(JQuery('#bfPayPalTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfPayPalName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}

							if (error == '') {

								appScope.optionElement.title = JQuery.trim(JQuery('#bfPayPalTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfPayPalName').get(0).value);

								if (JQuery('#bfPayPalTestaccountYes').get(0).checked) {
									appScope.optionElement.options.testaccount = true;
								} else {
									appScope.optionElement.options.testaccount = false;
								}

								if (JQuery('#bfPayPalDownloadableFileYes').get(0).checked) {
									appScope.optionElement.options.downloadableFile = true;
								} else {
									appScope.optionElement.options.downloadableFile = false;
								}

								if (typeof appScope.optionElement.options.useIpn == "undefined") { // compat 730
									appScope.optionElement.option['useIpn'] = false;
								}

								if (JQuery('#bfPayPalUseIpnYes').get(0).checked) {
									appScope.optionElement.options.useIpn = true;
								} else {
									appScope.optionElement.options.useIpn = false;
								}

								appScope.optionElement.options.filepath = JQuery.trim(JQuery('#bfPayPalFilepath').get(0).value);
								appScope.optionElement.options.downloadTries = JQuery.trim(JQuery('#bfPayPalFileDownloadTries').get(0).value);
								appScope.optionElement.options.business = JQuery.trim(JQuery('#bfPayPalBusiness').get(0).value);
								appScope.optionElement.options.token = JQuery.trim(JQuery('#bfPayPalToken').get(0).value);
								appScope.optionElement.options.testBusiness = JQuery.trim(JQuery('#bfPayPalTestBusiness').get(0).value);
								appScope.optionElement.options.testToken = JQuery.trim(JQuery('#bfPayPalTestToken').get(0).value);
								appScope.optionElement.options.itemname = JQuery.trim(JQuery('#bfPayPalItemname').get(0).value);
								appScope.optionElement.options.itemnumber = JQuery.trim(JQuery('#bfPayPalItemnumber').get(0).value);
								appScope.optionElement.options.amount = JQuery.trim(JQuery('#bfPayPalAmount').get(0).value);
								appScope.optionElement.options.tax = JQuery.trim(JQuery('#bfPayPalTax').get(0).value);
								appScope.optionElement.options.thankYouPage = JQuery.trim(JQuery('#bfPayPalThankYouPage').get(0).value);
								appScope.optionElement.options.locale = JQuery.trim(JQuery('#bfPayPalLocale').get(0).value);
								appScope.optionElement.options.currencyCode = JQuery.trim(JQuery('#bfPayPalCurrencyCode').get(0).value);
								JQuery('#' + appScope.optionElement.id).attr('src', JQuery.trim(JQuery('#bfPayPalImage').get(0).value));
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfPayPalWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfPayPalHeight').get(0).value);
							}

							break;


						case 'bfSofortueberweisung':

							if (JQuery.trim(JQuery('#bfSofortueberweisungTitle').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_TITLE_MUST_NOT_BE_EMPTY') ?>';
							}
							if (JQuery.trim(JQuery('#bfSofortueberweisungName').get(0).value) == '') {
								error = '<?php echo BFText::_('COM_BREEZINGFORMS_NAME_MUST_NOT_BE_EMPTY') ?>';
							}

							if (error == '') {

								appScope.optionElement.title = JQuery.trim(JQuery('#bfSofortueberweisungTitle').get(0).value);
								appScope.optionElement.name = JQuery.trim(JQuery('#bfSofortueberweisungName').get(0).value);

								if (JQuery('#bfSofortueberweisungDownloadableFileYes').get(0).checked) {
									appScope.optionElement.options.downloadableFile = true;
								} else {
									appScope.optionElement.options.downloadableFile = false;
								}

								if (JQuery('#bfSofortueberweisungMailbackYes').get(0).checked) {
									appScope.optionElement.options.mailback = true;
								} else {
									appScope.optionElement.options.mailback = false;
								}

								appScope.optionElement.options.filepath = JQuery.trim(JQuery('#bfSofortueberweisungFilepath').get(0).value);
								appScope.optionElement.options.downloadTries = JQuery.trim(JQuery('#bfSofortueberweisungFileDownloadTries').get(0).value);
								appScope.optionElement.options.user_id = JQuery.trim(JQuery('#bfSofortueberweisungUserId').get(0).value);
								appScope.optionElement.options.project_id = JQuery.trim(JQuery('#bfSofortueberweisungProjectId').get(0).value);
								appScope.optionElement.options.project_password = JQuery.trim(JQuery('#bfSofortueberweisungProjectPassword').get(0).value);
								appScope.optionElement.options.reason_1 = JQuery.trim(JQuery('#bfSofortueberweisungReason1').get(0).value);
								appScope.optionElement.options.reason_2 = JQuery.trim(JQuery('#bfSofortueberweisungReason2').get(0).value);
								appScope.optionElement.options.amount = JQuery.trim(JQuery('#bfSofortueberweisungAmount').get(0).value);
								appScope.optionElement.options.thankYouPage = JQuery.trim(JQuery('#bfSofortueberweisungThankYouPage').get(0).value);
								appScope.optionElement.options.language_id = JQuery.trim(JQuery('#bfSofortueberweisungLanguageId').get(0).value);
								appScope.optionElement.options.currency_id = JQuery.trim(JQuery('#bfSofortueberweisungCurrencyId').get(0).value);
								JQuery('#' + appScope.optionElement.id).attr('src', JQuery.trim(JQuery('#bfSofortueberweisungImage').get(0).value));
								JQuery('#' + appScope.optionElement.id).get(0).style.width = JQuery.trim(JQuery('#bfSofortueberweisungWidth').get(0).value);
								JQuery('#' + appScope.optionElement.id).get(0).style.height = JQuery.trim(JQuery('#bfSofortueberweisungHeight').get(0).value);
							}

							break;

					}

					if (JQuery('#' + appScope.optionElement.id).parent().attr('class') == 'ui-wrapper') {

						element = JQuery('#' + appScope.optionElement.id).parent();

						if (JQuery('#' + appScope.optionElement.id).get(0).style.width != '') {
							element.css('width', JQuery('#' + appScope.optionElement.id).get(0).style.width);
						}
						if (JQuery('#' + appScope.optionElement.id).get(0).style.height != '') {
							element.css('height', JQuery('#' + appScope.optionElement.id).get(0).style.height);
						}
					}

				} else {
					// must be a label then
					JQuery(appScope.optionElement).resizable('destroy');
					JQuery(appScope.optionElement).get(0).innerHTML = JQuery.trim(JQuery('#bfLabelContent').get(0).value);
					appScope.setElementResizable(JQuery(appScope.optionElement).get(0));
					JQuery(appScope.optionElement).get(0).style.width = JQuery.trim(JQuery('#bfLabelWidth').get(0).value);
					JQuery(appScope.optionElement).get(0).style.height = JQuery.trim(JQuery('#bfLabelHeight').get(0).value);
					if (JQuery('#bfLabelOnTop').get(0).checked) {
						JQuery(appScope.optionElement).parent().children('.ff_break')[0].style.display = '';
						JQuery(appScope.optionElement).parent().children('.ff_label')[0].style.cssFloat = 'none';
					} else {
						JQuery(appScope.optionElement).parent().children('.ff_break')[0].style.display = 'none';
						JQuery(appScope.optionElement).parent().children('.ff_label')[0].style.cssFloat = 'left';
					}
				}


				JQuery('#' + appScope.optionElement.id).get(0).style.padding = JQuery.trim(JQuery('#bfOptionsPadding').get(0).value);
				JQuery('#' + appScope.optionElement.id).get(0).style.margin = JQuery.trim(JQuery('#bfOptionsMargin').get(0).value);
				appScope.optionElement.orderNumber = JQuery.trim(JQuery('#bfOptionsOrder').get(0).value);
				appScope.optionElement.tabIndex = JQuery.trim(JQuery('#bfOptionsTabIndex').get(0).value);

				if (error == '') {
					JQuery('#bfOptionsSaveMessage').get(0).innerHTML = '<?php echo BFText::_('COM_BREEZINGFORMS_OPTIONS_SAVED_TO_FINALLY_SAVE_YOUR_FORM_CLICK_SAVE_ON_THE_TOP_RIGHT_BUTTON') ?><br/><br/>';
				} else {
					JQuery('#bfOptionsSaveMessage').get(0).innerHTML = error + '<br/><br/>';
				}

				JQuery('#bfOptionsSaveMessage').css('display', '');
				JQuery('#bfOptionsSaveMessage').css('visibility', 'visible');
			}
		);

		this.droppableAreaSortableInit = {
			cursor: "pointer",
			handle: '.ff_dragBox',
			tolerance: 'pointer',
			connectWith: ['#trashcan', '.droppableArea'],
			start: function (e, ui) {

			},
			receive: function (e, ui) {

				// removing the moved element from other areas and assigning to new one.
				// this keeps the internal areaList intact

				var foundElements = new Array();

				if (!appScope.hasArea(JQuery(this).get(0))) {
					appScope.areaList.push({ area: JQuery(this).get(0), elements: [] });
				}

				if (appScope.hasArea(JQuery(this).get(0))) {

					// try to find the elements to be moved on all areas
					for (var i = 0; i < appScope.areaList.length; i++) {
						if (appScope.areaList[i].area != JQuery(this).get(0)) {
							for (var j = 0; j < appScope.areaList[i].elements.length; j++) {
								for (var k = 0; k < JQuery(ui.item).children('.ff_div').children('.ff_elem').length; k++) {
									if (appScope.areaList[i].elements[j].id == JQuery(ui.item).children('.ff_div').children('.ff_elem')[k].id) {
										foundElements.push(appScope.areaList[i].elements[j]);
										break;
									}
								}
								for (var k = 0; k < JQuery(ui.item).children('.ff_div').children('.ui-wrapper').length; k++) {
									var wrappedElement = JQuery(ui.item).children('.ff_div').children('.ui-wrapper')[k];
									if (JQuery(wrappedElement).children('.ff_elem').length == 1) {
										var id = JQuery(wrappedElement).children('.ff_elem')[0].id;
										if (appScope.areaList[i].elements[j].id == id) {
											foundElements.push(appScope.areaList[i].elements[j]);
											break;
										}
									}
								}
							}
						}
					}

					// try to find them in the trashcan
					for (var i = 0; i < appScope.trashcanAreaList.length; i++) {
						for (var k = 0; k < JQuery(ui.item).children('.ff_div').children('.ff_elem').length; k++) {
							if (appScope.trashcanAreaList[i].id == JQuery(ui.item).children('.ff_div').children('.ff_elem')[k].id) {
								foundElements.push(appScope.trashcanAreaList[i]);
								break;
							}
						}

						for (var k = 0; k < JQuery(ui.item).children('.ff_div').children('.ui-wrapper').length; k++) {
							var wrappedElement = JQuery(ui.item).children('.ff_div').children('.ui-wrapper')[k];
							if (JQuery(wrappedElement).children('.ff_elem').length == 1) {
								var id = JQuery(wrappedElement).children('.ff_elem')[0].id;
								if (appScope.areaList[i].elements[j].id == id) {
									foundElements.push(appScope.trashcanAreaList[i]);
									break;
								}
							}
						}
					}

					if (foundElements.length > 0) {
						for (var i = 0; i < foundElements.length; i++) {
							// remove the element from all areas and the trashcan...
							appScope.removeElementFromAreaList(foundElements[i]);
							appScope.removeElementFromTrashcanAreaList(foundElements[i]);
							// ...and add it to the newly selected area
							foundElements[i].area = JQuery(this).get(0).id;
							var elements = appScope.getElementsArray(JQuery(this).get(0));
							elements.push(foundElements[i]);
						}
					}
				}
			}
		};
		JQuery(".droppableArea").sortable(appScope.droppableAreaSortableInit);

		JQuery("#trashcan").sortable(
			{
				cursor: "pointer",
				handle: '.ff_dragBox',
				connectWith: ['.droppableArea'],
				receive: function (e, ui) {

					var foundElements = new Array();

					for (var i = 0; i < appScope.areaList.length; i++) {
						if (appScope.areaList[i].area != JQuery(this).get(0)) {
							for (var j = 0; j < appScope.areaList[i].elements.length; j++) {
								for (var k = 0; k < JQuery(ui.item).children('.ff_div').children('.ff_elem').length; k++) {
									if (appScope.areaList[i].elements[j].id == JQuery(ui.item).children('.ff_div').children('.ff_elem')[k].id) {
										foundElements.push(appScope.areaList[i].elements[j]);
										break;
									}
								}
								for (var k = 0; k < JQuery(ui.item).children('.ff_div').children('.ui-wrapper').length; k++) {
									var wrappedElement = JQuery(ui.item).children('.ff_div').children('.ui-wrapper')[k];
									if (JQuery(wrappedElement).children('.ff_elem').length == 1) {
										var id = JQuery(wrappedElement).children('.ff_elem')[0].id;
										if (appScope.areaList[i].elements[j].id == id) {
											foundElements.push(appScope.areaList[i].elements[j]);
											break;
										}
									}
								}
							}
						}
					}

					if (foundElements.length > 0) {
						for (var i = 0; i < foundElements.length; i++) {
							appScope.removeElementFromAreaList(foundElements[i]);
							appScope.trashcanAreaList.push(foundElements[i]);
						}
					}
				}
			}
		);

		JQuery(".draggableElement").draggable(
			{
				revert: 'invalid',
				helper: 'clone',
				opacity: appScope.opacity

			}
		);

		this.droppableAreaDroppableInit = {
			accept: '.draggableElement',
			activeClass: 'droppable-active',
			hoverClass: 'droppable-hover',
			drop: function (e, ui) {

				var element = null;

				if (typeof JQuery(this).get(0).id == "undefined" || JQuery(this).get(0).id == "") {
					alert('One of your areas has no id, please fix that!');
					return;
				}

				if (!appScope.hasArea(JQuery(this).get(0))) {
					appScope.areaList.push({ area: JQuery(this).get(0), elements: [] });
				}

				var elements = appScope.getElementsArray(JQuery(this).get(0));
				var rndId = JQuery.md5(Math.random() + elements.length + JQuery(ui.draggable).attr('id') + JQuery(this).get(0).id);
				var internalType = JQuery(ui.draggable).attr('id');

				element = appScope.createElementByType(internalType, rndId);

				if (element != null) {

					if (JQuery(ui.draggable).attr('id') != 'bfHidden') {

						var wrapper = document.createElement('div');
						wrapper.setAttribute('id', 'ff_div' + rndId);
						wrapper.setAttribute('class', 'ff_div');

						if (JQuery(ui.draggable).attr('id') != 'bfStaticText') {

							var label = document.createElement('div');
							label.setAttribute('id', 'ff_label' + rndId);
							label.setAttribute('class', 'ff_label');

							label.style.verticalAlign = 'top';
							label.style.width = '50px';
							label.style.height = '10px';
							label.style.cssFloat = 'left';
							label.innerHTML = 'Label...';
							JQuery(label).click(appScope.resizableElement);

							JQuery(wrapper).append(label);

							var mybr = document.createElement('div');
							mybr.setAttribute('id', 'ff_break' + rndId);
							mybr.setAttribute('class', 'ff_break');
							JQuery(mybr).css('display', 'none');

							JQuery(wrapper).append(mybr);
						}

						JQuery(wrapper).append(element.element);

						var listItem = document.createElement('li');
						listItem.setAttribute('id', 'ff_listItem' + rndId);
						listItem.setAttribute('class', 'ff_listItem');

						var dragBox = document.createElement('span');
						dragBox.setAttribute('id', 'ff_dragBox' + rndId);
						dragBox.setAttribute('class', 'ff_dragBox');
						dragBox.innerHTML = "&nbsp;";

						JQuery(listItem).append(dragBox);
						JQuery(listItem).append(wrapper);

						var appender = document.createElement('div');
						appender.setAttribute('class', 'ff_appender');
						JQuery(appender).css('clear', 'both');
						JQuery(listItem).append(appender);

						JQuery(this).append(listItem);

					} else {

						JQuery('#bfTemplate').append(element.element);

					}
					// push the element into the internal representation
					if (elements != null) {
						elements.push(
							{
								id: element.element.id,
								dbId: 0,
								rndId: rndId,
								name: rndId, // default name until changed by user
								title: 'title_' + element.element.id, // default title until changed by user
								type: element.element.type ? element.element.type : '',
								internalType: internalType,
								bfType: element.bfType,
								elementType: element.elementType,
								area: JQuery(this).get(0).id,
								appElementId: JQuery(ui.draggable).attr('id'),
								appElementOrderId: elements.length,
								wrapperId: wrapper ? wrapper.id : '',
								labelId: label ? label.id : '',
								listItemId: listItem ? listItem.id : '',
								data1: element.data1,
								data2: element.data2,
								data3: element.data3,
								script1cond: element.script1cond,
								script1id: element.script1id,
								script1code: element.script1code,
								script1flag1: element.script1flag1,
								script1flag2: element.script1flag2,
								script2cond: element.script2cond,
								script2id: element.script2id,
								script2code: element.script2code,
								script2flag1: element.script2flag1,
								script2flag2: element.script2flag2,
								script2flag3: element.script2flag3,
								script2flag4: element.script2flag4,
								script2flag5: element.script2flag5,
								script3cond: element.script3cond,
								script3id: element.script3id,
								script3code: element.script3code,
								script3msg: element.script3msg,
								functionNameScript1: element.functionNameScript1,
								functionNameScript2: element.functionNameScript2,
								functionNameScript3: element.functionNameScript3,
								flag1: element.flag1,
								flag2: element.flag2,
								mailback: element.mailback,
								mailbackfile: element.mailbackfile,
								mailbackAsSender: element.mailbackAsSender,
								mailbackAccept: false,
								mailbackAcceptConnectWith: '',
								orderNumber: -1,
								tabIndex: -1,
								page: parseInt(document.adminForm.page.value),
								options: element.options
							}
						);

						if (internalType == 'bfCaptcha') {
							appScope.captchaAdded++;
						}

						appScope.populateHiddenFieldsOptions();
						if (internalType != 'bfHidden') {
							appScope.setElementResizable(element.element);
						}
						var labelsResult = JQuery('#' + element.element.id).parent().parent().children('.ff_label');
						if (labelsResult.length != 0) {
							for (var k = 0; k < labelsResult.length; k++) {
								appScope.setElementResizable(labelsResult[k]);
							}
						}
						labelsResult = JQuery('#' + element.element.id).parent().children('.ff_label');
						if (labelsResult.length != 0) {
							for (var k = 0; k < labelsResult.length; k++) {
								appScope.setElementResizable(labelsResult[k]);
							}
						}
						appScope.initMouseOvers();
					}
				}
			}
		};
		JQuery(".droppableArea").droppable(appScope.droppableAreaDroppableInit);
	}

	JQuery(document).ready(function () {
		app = new BF_EasyModeApp();
		app.populateHiddenFieldsOptions();
	});

	var bf_submitbutton = function (pressbutton) {
		var form = document.adminForm;

		switch (pressbutton) {
			case 'close':
				location.href = "index.php?option=com_breezingforms&act=manageforms";
				break;
			case 'integrate':
				form.task.value = 'list';
				form.act.value = 'integrate';
				submitform(pressbutton);
				break;
			case 'save':
				var prepared = app.prepareForSave();
				form.task.value = 'save';
				form.act.value = 'easymode';
				form.templateCode.value = prepared.templateCode;
				form.areas.value = prepared.areas;
				submitform(pressbutton);
				break;
			case 'editform':
				location.href = "index.php?option=com_breezingforms&task=editform&act=editpage&form=<?php echo $formId ?>";
				break;
			case 'preview':
				SqueezeBox.initialize({});

				SqueezeBox.loadModal = function (modalUrl, handler, x, y) {
					this.presets.size.x = 820;
					this.initialize();
					var options = JQuery.toJSON("{handler: \'" + handler + "\', size: {x: " + x + ", y: " + y + "}}");
					this.setOptions(this.presets, options);
					this.assignOptions();
					this.setContent(handler, modalUrl);
				};

				SqueezeBox.loadModal("<?php echo Uri::root() ?>index.php?format=html&tmpl=component&option=com_breezingforms&ff_form=<?php echo $formId ?>&ff_page=<?php echo $page ?>", "iframe", 820, 400);
				break;
			case 'preview_site':
				SqueezeBox.initialize({});

				SqueezeBox.loadModal = function (modalUrl, handler, x, y) {
					this.presets.size.x = 1024;
					this.initialize();
					var options = JQuery.toJSON("{handler: \'" + handler + "\', size: {x: " + x + ", y: " + y + "}}");
					this.setOptions(this.presets, options);
					this.assignOptions();
					this.setContent(handler, modalUrl);
				};

				SqueezeBox.loadModal("<?php echo Uri::root() ?>index.php?option=com_breezingforms&ff_form=<?php echo $formId ?>&ff_page=<?php echo $page ?>", "iframe", 1024, 400);
				break;
			default:
				break;
		}


	}; // submitbutton

	if (typeof Joomla != "undefined") {
		Joomla.submitbutton = bf_submitbutton;
	}

	submitbutton = bf_submitbutton;

	function expstring(text) {
		text = JQuery.trim(text);
		var i;
		var o = '';
		for (i = 0; i < text.length; i++) {
			c = text.charAt(i);
			switch (c) {
				case '&': o += '\\x26'; break;
				case '<': o += '\\x3C'; break;
				case '>': o += '\\x3E'; break;
				case '\'': o += '\\x27'; break;
				case '\\': o += '\\x5C'; break;
				case '"': o += '\\x22'; break;
				case '\n': o += '\\n'; break;
				case '\r': o += '\\r'; break;
				default: o += c;
			} // switch
		} // for
		return o;
	} // expstring

	function createActionCode(element) {
		form = document.bfForm;
		name = element.name;
		if (name == '') {
			alert('Please enter the element name first.');
			return;
		} // if
		if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREAACTION'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>")) return;
		code =
			"function ff_" + name + "_action(element, action)\n" +
			"{\n" +
			"    switch (action) {\n";
		if (form.script2flag1)
			if (form.script2flag1.checked)
				code +=
					"        case 'click':\n" +
					"            break;\n";
		if (form.script2flag2)
			if (form.script2flag2.checked)
				code +=
					"        case 'blur':\n" +
					"            break;\n";
		if (form.script2flag3)
			if (form.script2flag3.checked)
				code +=
					"        case 'change':\n" +
					"            break;\n";
		if (form.script2flag4)
			if (form.script2flag4.checked)
				code +=
					"        case 'focus':\n" +
					"            break;\n";
		if (form.script2flag5)
			if (form.script2flag5.checked)
				code +=
					"        case 'select':\n" +
					"            break;\n";
		code +=
			"        default:;\n" +
			"    } // switch\n" +
			"} // ff_" + name + "_action\n";

		oldcode = form.script2code.value;
		if (oldcode != '')
			form.script2code.value =
				code +
				"\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
				oldcode;
		else
			form.script2code.value = code;
	} // createActionCode

	function createInitCode(element) {
		form = document.bfForm;
		name = element.name;
		if (name == '') {
			alert('Please enter the element name first.');
			return;
		} // if
		if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREAINIT'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>")) return;
		code =
			"function ff_" + name + "_init(element, condition)\n" +
			"{\n" +
			"    switch (condition) {\n";
		if (form.script1flag1.checked)
			code +=
				"        case 'formentry':\n" +
				"            break;\n";
		if (form.script1flag2.checked)
			code +=
				"        case 'pageentry':\n" +
				"            break;\n";
		code +=
			"        default:;\n" +
			"    } // switch\n" +
			"} // ff_" + name + "_init\n";
		oldcode = form.script1code.value;
		if (oldcode != '')
			form.script1code.value =
				code +
				"\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
				oldcode;
		else
			form.script1code.value = code;
	} // createInitCode

	function createValidationCode(element) {
		form = document.bfForm;
		name = element.name;
		if (name == '') {
			alert('Please enter the element name first.');
			return;
		} // if
		if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREAVALID'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>")) return;
		code =
			"function ff_" + name + "_validation(element, message)\n" +
			"{\n" +
			"    if (element_fails_my_test) {\n" +
			"        if (message=='') message = element.name+\" faild in my test.\\n\"\n" +
			"        ff_validationFocus(element.name);\n" +
			"        return message;\n" +
			"    } // if\n" +
			"    return '';\n" +
			"} // ff_" + name + "_validation\n";
		oldcode = form.script3code.value;
		if (oldcode != '')
			form.script3code.value =
				code +
				"\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
				oldcode;
		else
			form.script3code.value = code;
	} // createValidationCode

	function stripHTML(string) {
		return string.replace(/<(.|\n)*?>/g, '');
	}
</script>