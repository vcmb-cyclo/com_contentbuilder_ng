<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 5.0
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 * */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class QuickModeHtml
{

    public static function showApplication($formId, $formName, $formTitle, $formDesc, $formEmailntf, $formEmailadr, $dataObjectString, $elementScripts, $themes, $themesbootstrap, $themesbootstrap3)
    {
        $active_language_code = htmlentities(BFRequest::getVar('active_language_code', ''), ENT_QUOTES, 'UTF-8');
        HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
        JHTML::_('behavior.keepalive');
        $iconBase = '../administrator/components/com_breezingforms/libraries/jquery/themes/quickmode/i/';
        Factory::getDocument()->addStyleSheet(Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/themes/quickmode/quickmode.all.css');
        Factory::getDocument()->addStyleSheet(Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/jtree/tree_component.css');
        Factory::getDocument()->addStyleSheet(Uri::root() . 'administrator/components/com_breezingforms/admin/style.css');
        ?>

        <script type="text/javascript"
            src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/jtree/'; ?>_lib.js"></script>
        <script type="text/javascript"
            src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/jtree/'; ?>tree_component.min.js"></script>
        <script type="text/javascript"
            src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/'; ?>jq-ui.min.js"></script>
        <script type="text/javascript"
            src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/plugins/bas'; ?>e64.js"></script>
        <script type="text/javascript"
            src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/plugins/'; ?>json.js"></script>
        <script type="text/javascript"
            src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/plugins/'; ?>md5.js"></script>
        <script type="text/javascript"
            src="<?php echo Uri::root() ?>components/com_breezingforms/libraries/jquery/center.js"></script>

        <script type="text/javascript"
            src="<?php echo Uri::root() . 'administrator/components/com_breezingforms/libraries/jquery/'; ?>jquery.scroll.js"></script>



        <script type="text/javascript">

            String.prototype.bfendsWith = function (suffix) {
                return this.match(suffix + "$") == suffix;
            };

            var app = null;

            function BF_QuickModeApp() {

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

                var selectedTreeElement = null;
                var copyTreeElement = null;
                var appScope = this;
                this.elementScripts = <?php echo Zend_Json::encode($elementScripts) ?>;
                this.dataObject = <?php echo str_replace("..\\/administrator\\/components\\/com_facileforms", "..\\/administrator\\/components\\/com_breezingforms", $dataObjectString) ?>;

                <?php require_once(JPATH_SITE . '/administrator/components/com_breezingforms/admin/quickmode-elements-js.php'); ?>

                /**
                 Helper methods
                 */
                this.getNodeClass = function (node) {
                    if (JQuery(node).attr('class')) {
                        var splitted = JQuery(appScope.selectedTreeElement).attr('class').split(' ');
                        if (splitted.length != 0) {
                            return splitted[0];
                        }
                    }
                    return '';
                };

                this.setProperties = function (node, props) {
                    var item = this.findDataObjectItem(JQuery(node).attr('id'), appScope.dataObject);
                    item.properties = props;
                };

                this.getProperties = function (node) {

                    var item = this.findDataObjectItem(JQuery(node).attr('id'), appScope.dataObject)
                    return item.properties;
                };

                /**
                 searches for the id in a given object item.
                 */
                this.findDataObjectItem = function (id, startObj) {
                    if (id && startObj && startObj.attributes && startObj.attributes.id) {
                        if (startObj.attributes.id == id) {
                            return startObj;
                        } else {
                            if (startObj.children) {
                                var child = null;
                                for (var i = 0; i < startObj.children.length; i++) {
                                    child = appScope.findDataObjectItem(id, startObj.children[i]);
                                    if (child) {
                                        return child;
                                    }
                                }
                            }
                        }
                        return null;
                    }
                    return null;
                };

                this.getItemsFlattened = function (startObj, arr) {
                    if (startObj && startObj.properties && startObj.properties.type == 'element') {
                        arr.push(startObj);

                    }
                    if (startObj.children) {
                        var child = null;
                        for (var i = 0; i < startObj.children.length; i++) {
                            appScope.getItemsFlattened(startObj.children[i], arr);
                        }
                    }
                };

                this.replaceDataObjectItem = function (id, replacement, startObj) {
                    if (id && startObj && startObj.attributes && startObj.attributes.id) {
                        if (startObj.children) {
                            var child = null;
                            for (var i = 0; i < startObj.children.length; i++) {
                                if (startObj.children[i].attributes.id == id) {
                                    startObj.children[i] = replacement;
                                    break;
                                }
                                appScope.replaceDataObjectItem(id, replacement, startObj.children[i]);
                            }
                        }
                    }
                }

                /**
                 searches for the id in a given object item and deletes it.
                 returns the deleted child.
                 */
                this.deleteDataObjectItem = function (id, startObj, previous) {
                    if (id && startObj && startObj.attributes && startObj.attributes.id) {
                        if (startObj.attributes.id == id) {
                            if (previous) {
                                var newChildren = new Array();
                                for (var j = 0; j < previous.children.length; j++) {
                                    if (previous.children[j].attributes.id != startObj.attributes.id) {
                                        newChildren.push(previous.children[j]);
                                    }
                                }
                                previous.children = newChildren;
                            }
                            return startObj;
                        } else {
                            if (startObj.children) {
                                var child = null;
                                for (var i = 0; i < startObj.children.length; i++) {
                                    child = appScope.deleteDataObjectItem(id, startObj.children[i], startObj);
                                    if (child) {
                                        return child;
                                    }
                                }
                            }
                        }
                        return null;
                    }
                    return null;
                };

                this.moveDataObjectItem = function (sourceId, targetId, index, obj) {
                    var source = appScope.deleteDataObjectItem(sourceId, obj);
                    var target = appScope.findDataObjectItem(targetId, obj);
                    if (target && !target.children && (target.attributes['class'] == 'bfQuickModePageClass' || target.attributes['class'] == 'bfQuickModeSectionClass' || target.attributes['class'] == 'bfQuickModeRootClass')) {
                        target.children = new Array();
                    }
                    if (target && target.children) {
                        target.children.splice(index, 0, source);
                        if (target.attributes['class'] == 'bfQuickModeRootClass') {
                            for (var i = 0; i < target.children.length; i++) {
                                var mdata = appScope.getProperties(JQuery('#' + target.children[i].attributes.id));
                                if (mdata) {
                                    if (target.children[i].attributes['class'] == 'bfQuickModePageClass') {
                                        target.children[i].attributes.id = 'bfQuickModePage' + (i + 1);
                                        target.children[i].data.title = "<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_PAGE')) ?> " + (i + 1);
                                        target.children[i].properties.pageNumber = i + 1;
                                    }
                                }
                            }
                        }
                        return true;
                    }
                    return false;
                };

                this.insertElementInto = function (source, target) {
                    if (target && target.children) {
                        if (target.attributes['class'] == 'bfQuickModeSectionClass' || target.attributes['class'] == 'bfQuickModePageClass') {
                            this.recreatedIds(source);
                            target.children.push(source);
                        }
                    }
                };

                this.recreatedIds = function (startObj) {
                    if (startObj && startObj.attributes && startObj.attributes.id) {
                        if (startObj.attributes['class'] == 'bfQuickModeSectionClass') {
                            type = 'bfQuickModeSection';
                        } else {
                            type = 'bfQuickMode';
                        }
                        var id = type + (Math.floor(Math.random() * 100000));
                        startObj.attributes.id = id;
                        if (startObj.attributes['class'] == 'bfQuickModeSectionClass') {
                            startObj.properties.name = id;
                        } else {
                            startObj.properties.bfName = id;
                            startObj.properties.dbId = 0;
                        }
                        startObj.properties.name = id;
                        if (startObj.children) {
                            var child = null;
                            for (var i = 0; i < startObj.children.length; i++) {
                                child = appScope.recreatedIds(startObj.children[i]);
                                if (child) {
                                    return child;
                                }
                            }
                        }
                        return null;
                    }
                    return null;
                };

                /**
                 Element properties
                 */

                // TEXTFIELD
                this.saveTextProperties = function (mdata, item) {
                    mdata.value = JQuery('#bfElementTypeTextValue').val();
                    mdata['value_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeTextValueTrans').val();
                    mdata.placeholder = JQuery('#bfElementTypeTextPlaceholder').val();
                    mdata['placeholder_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeTextPlaceholderTrans').val();
                    mdata.bfName = JQuery('#bfElementName').val();
                    mdata.logging = JQuery('#bfElementAdvancedLogging').attr('checked');
                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();
                    mdata.maxLength = JQuery('#bfElementTypeTextMaxLength').val();
                    mdata.icon = JQuery('#bfElementTypeTextIcon').val();

                    mdata.hint = JQuery('#bfElementTypeTextHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeTextHintTrans').val();

                    mdata.password = JQuery('#bfElementAdvancedPassword').attr('checked');
                    mdata.readonly = JQuery('#bfElementAdvancedReadOnly').attr('checked');
                    mdata.mailback = JQuery('#bfElementAdvancedMailback').attr('checked');
                    mdata.mailbackAsSender = JQuery('#bfElementAdvancedMailbackAsSender').attr('checked');
                    mdata.mailbackfile = JQuery('#bfElementAdvancedMailbackfile').val();
                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.hideLabel = JQuery('#bfElementAdvancedHideLabel').attr('checked');
                    mdata.size = JQuery('#bfElementTypeTextSize').val();
                    mdata.orderNumber = JQuery('#bfElementOrderNumber').val();
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');
                    item.properties = mdata;
                };

                this.populateTextProperties = function (mdata) {

                    JQuery('#bfElementTypeTextValue').val(mdata.value);
                    JQuery('#bfElementTypeTextValueTrans').val(typeof mdata['value_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['value_translation<?php echo $active_language_code; ?>'] : "");

                    if (typeof mdata.placeholder == "undefined") {
                        mdata['placeholder'] = '';
                    }
                    JQuery('#bfElementTypeTextPlaceholder').val(mdata.placeholder);
                    JQuery('#bfElementTypeTextPlaceholderTrans').val(typeof mdata['placeholder_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['placeholder_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementName').val(mdata.bfName);
                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");
                    JQuery('#bfElementAdvancedLogging').attr('checked', mdata.logging);
                    JQuery('#bfElementTypeTextMaxLength').val(mdata.maxLength);

                    JQuery('#bfElementTypeTextHint').val(mdata.hint);
                    JQuery('#bfElementTypeTextHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedPassword').attr('checked', mdata.password);
                    JQuery('#bfElementAdvancedReadOnly').attr('checked', mdata.readonly);
                    JQuery('#bfElementAdvancedMailback').attr('checked', mdata.mailback);
                    JQuery('#bfElementAdvancedMailbackAsSender').attr('checked', mdata.mailbackAsSender);
                    JQuery('#bfElementAdvancedMailbackfile').val(mdata.mailbackfile);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    JQuery('#bfElementTypeTextSize').val(mdata.size);
                    JQuery('#bfElementOrderNumber').val(mdata.orderNumber);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);

                    JQuery('#bfElementTypeTextIcon').val(mdata.icon);
                };

                // TEXTAREA
                this.saveTextareaProperties = function (mdata, item) {
                    mdata.value = JQuery('#bfElementTypeTextareaValue').val();
                    mdata['value_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeTextareaValueTrans').val();

                    mdata.placeholder = JQuery('#bfElementTypeTextareaPlaceholder').val();
                    mdata['placeholder_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeTextareaPlaceholderTrans').val();

                    mdata.is_html = JQuery('#bfElementTypeTextareaIsHtml').attr('checked');
                    mdata.bfName = JQuery('#bfElementName').val();
                    mdata.logging = JQuery('#bfElementTextareaAdvancedLogging').attr('checked');

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.hint = JQuery('#bfElementTypeTextareaHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeTextareaHintTrans').val();
                    mdata.icon = JQuery('#bfElementTypeTextareaIcon').val();


                    mdata.width = JQuery('#bfElementTypeTextareaWidth').val();
                    mdata.height = JQuery('#bfElementTypeTextareaHeight').val();
                    mdata.maxlength = JQuery('#bfElementTypeTextareaMaxLength').val();
                    mdata.showMaxlengthCounter = JQuery('#bfElementTypeTextareaMaxLengthShow').attr('checked');
                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.hideLabel = JQuery('#bfElementTextareaAdvancedHideLabel').attr('checked');
                    mdata.orderNumber = JQuery('#bfElementTextareaAdvancedOrderNumber').val();
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');
                    item.properties = mdata;
                };

                this.populateTextareaProperties = function (mdata) {
                    JQuery('#bfElementTypeTextareaValue').val(mdata.value);
                    JQuery('#bfElementTypeTextareaValueTrans').val(typeof mdata['value_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['value_translation<?php echo $active_language_code; ?>'] : "");

                    if (typeof mdata.placeholder == "undefined") {
                        mdata['placeholder'] = '';
                    }
                    JQuery('#bfElementTypeTextareaPlaceholder').val(mdata.placeholder);
                    JQuery('#bfElementTypeTextareaPlaceholderTrans').val(typeof mdata['placeholder_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['placeholder_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeTextareaIsHtml').attr('checked', mdata.is_html);
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTextareaAdvancedLogging').attr('checked', mdata.logging);

                    JQuery('#bfElementTypeTextareaHint').val(mdata.hint);
                    JQuery('#bfElementTypeTextareaHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementTextareaAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    JQuery('#bfElementTypeTextareaWidth').val(mdata.width);
                    JQuery('#bfElementTypeTextareaHeight').val(mdata.height);
                    JQuery('#bfElementTypeTextareaIsHtml').val(mdata.is_html);
                    // compat 723
                    if (typeof mdata.maxlength == "undefined") {
                        mdata["maxlength"] = 0;
                    }
                    if (typeof mdata.showMaxlengthCounter == "undefined") {
                        mdata["showMaxlengthCounter"] = true;
                    }
                    // end compat 723
                    JQuery('#bfElementTypeTextareaMaxLength').val(!isNaN(mdata.maxlength) ? mdata.maxlength : 0);
                    JQuery('#bfElementTypeTextareaMaxLengthShow').attr('checked', mdata.showMaxlengthCounter);
                    JQuery('#bfElementTextareaAdvancedOrderNumber').val(mdata.orderNumber);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);

                    JQuery('#bfElementTypeTextareaIcon').val(mdata.icon);
                };

                // NUMBER ELEMENT
                this.saveNumberInputProperties = function (mdata, item) {
                    mdata.value = JQuery('#bfElementTypeNumberInputValue').val();
                    mdata['value_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeNumberInputValueTrans').val();
                    mdata.placeholder = JQuery('#bfElementTypeNumberInputPlaceholder').val();
                    mdata['placeholder_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeNumberInputPlaceholderTrans').val();
                    mdata.bfName = JQuery('#bfElementName').val();
                    mdata.logging = JQuery('#bfElementNumberInputAdvancedLogging').attr('checked');
                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();
                    mdata.maxLength = JQuery('#bfElementTypeNumberInputMaxLength').val();
                    mdata.icon = JQuery('#bfElementTypeNumberInputIcon').val();

                    mdata.hint = JQuery('#bfElementTypeNumberInputHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeNumberInputHintTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.hideLabel = JQuery('#bfElementNumberInputAdvancedHideLabel').attr('checked');
                    mdata.size = JQuery('#bfElementTypeNumberInputSize').val();
                    mdata.orderNumber = JQuery('#bfElementNumberInputOrderNumber').val();
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');

                    mdata.step = JQuery('#bfElementNumberInputAdvancedStep').val();
                    mdata.min = JQuery('#bfElementNumberInputAdvancedMin').val();
                    mdata.max = JQuery('#bfElementNumberInputAdvancedMax').val();

                    item.properties = mdata;
                };

                this.populateNumberInputProperties = function (mdata) {

                    JQuery('#bfElementTypeNumberInputValue').val(mdata.value);
                    JQuery('#bfElementTypeNumberInputValueTrans').val(typeof mdata['value_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['value_translation<?php echo $active_language_code; ?>'] : "");

                    if (typeof mdata.placeholder == "undefined") {
                        mdata['placeholder'] = '';
                    }
                    JQuery('#bfElementTypeNumberInputPlaceholder').val(mdata.placeholder);
                    JQuery('#bfElementTypeNumberInputPlaceholderTrans').val(typeof mdata['placeholder_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['placeholder_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementName').val(mdata.bfName);
                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");
                    JQuery('#bfElementNumberInputAdvancedLogging').attr('checked', mdata.logging);
                    JQuery('#bfElementTypeNumberInputMaxLength').val(mdata.maxLength);

                    JQuery('#bfElementTypeNumberInputHint').val(mdata.hint);
                    JQuery('#bfElementTypeNumberInputHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementNumberInputAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    JQuery('#bfElementTypeNumberInputSize').val(mdata.size);
                    JQuery('#bfElementNumberInputOrderNumber').val(mdata.orderNumber);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);

                    JQuery('#bfElementTypeNumberInputIcon').val(mdata.icon);

                    JQuery('#bfElementNumberInputAdvancedStep').val(mdata.step);
                    JQuery('#bfElementNumberInputAdvancedMin').val(mdata.min);
                    JQuery('#bfElementNumberInputAdvancedMax').val(mdata.max);
                };

                // RADIOS
                this.saveRadioGroupProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.group = JQuery('#bfElementTypeRadioGroupGroups').val();
                    mdata['group_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeRadioGroupGroupsTrans').val();

                    mdata.readonly = JQuery('#bfElementTypeRadioGroupReadonly').attr('checked');
                    mdata.wrap = JQuery('#bfElementTypeRadioGroupWrap').attr('checked');

                    mdata.hint = JQuery('#bfElementTypeRadioGroupHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeRadioGroupHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementRadioGroupAdvancedHideLabel').attr('checked');
                    mdata.logging = JQuery('#bfElementRadioGroupAdvancedLogging').attr('checked');
                    mdata.orderNumber = JQuery('#bfElementRadioGroupAdvancedOrderNumber').val();
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');

                    item.properties = mdata;
                };

                this.populateRadioGroupProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementTypeRadioGroupGroups').val(mdata.group);
                    JQuery('#bfElementTypeRadioGroupGroupsTrans').val(typeof mdata['group_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['group_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeRadioGroupReadonly').attr('checked', mdata.readonly);
                    JQuery('#bfElementTypeRadioGroupWrap').attr('checked', mdata.wrap);

                    JQuery('#bfElementTypeRadioGroupHint').val(mdata.hint);
                    JQuery('#bfElementTypeRadioGroupHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementRadioGroupAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    JQuery('#bfElementRadioGroupAdvancedLogging').attr('checked', mdata.logging);
                    JQuery('#bfElementRadioGroupAdvancedOrderNumber').val(mdata.orderNumber);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);
                };

                // Checkboxgroup
                this.saveCheckboxGroupProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.group = JQuery('#bfElementTypeCheckboxGroupGroups').val();
                    mdata['group_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCheckboxGroupGroupsTrans').val();

                    mdata.readonly = JQuery('#bfElementTypeCheckboxGroupReadonly').attr('checked');
                    mdata.wrap = JQuery('#bfElementTypeCheckboxGroupWrap').attr('checked');

                    mdata.hint = JQuery('#bfElementTypeCheckboxGroupHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCheckboxGroupHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementCheckboxGroupAdvancedHideLabel').attr('checked');
                    mdata.logging = JQuery('#bfElementCheckboxGroupAdvancedLogging').attr('checked');
                    mdata.orderNumber = JQuery('#bfElementCheckboxGroupAdvancedOrderNumber').val();
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');

                    item.properties = mdata;
                };

                this.populateCheckboxGroupProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementTypeCheckboxGroupGroups').val(mdata.group);
                    JQuery('#bfElementTypeCheckboxGroupGroupsTrans').val(typeof mdata['group_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['group_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeCheckboxGroupReadonly').attr('checked', mdata.readonly);
                    JQuery('#bfElementTypeCheckboxGroupWrap').attr('checked', mdata.wrap);

                    JQuery('#bfElementTypeCheckboxGroupHint').val(mdata.hint);
                    JQuery('#bfElementTypeCheckboxGroupHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementCheckboxGroupAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    JQuery('#bfElementCheckboxGroupAdvancedLogging').attr('checked', mdata.logging);
                    JQuery('#bfElementCheckboxGroupAdvancedOrderNumber').val(mdata.orderNumber);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);
                };

                // Signature
                this.saveSignatureProperties = function (mdata, item) {
                    // dynamic properties

                    mdata.hint = JQuery('#bfElementTypeSignatureHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeSignatureHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementSignatureAdvancedHideLabel').attr('checked');
                    mdata.logging = JQuery('#bfElementSignatureAdvancedLogging').attr('checked');
                    mdata.orderNumber = JQuery('#bfElementSignatureAdvancedOrderNumber').val();
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');

                    item.properties = mdata;
                };

                this.populateSignatureProperties = function (mdata) {
                    // dynamic properties

                    JQuery('#bfElementTypeSignatureHint').val(mdata.hint);
                    JQuery('#bfElementTypeSignatureHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementSignatureAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    JQuery('#bfElementSignatureAdvancedLogging').attr('checked', mdata.logging);
                    JQuery('#bfElementSignatureAdvancedOrderNumber').val(mdata.orderNumber);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);
                };

                // Checkbox
                this.saveCheckboxProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.value = JQuery('#bfElementTypeCheckboxValue').val() == '' ? 'checked' : JQuery('#bfElementTypeCheckboxValue').val();
                    mdata.checked = JQuery('#bfElementTypeCheckboxChecked').attr('checked');
                    mdata.readonly = JQuery('#bfElementTypeCheckboxReadonly').attr('checked');
                    mdata.mailbackAccept = JQuery('#bfElementCheckboxAdvancedMailbackAccept').attr('checked');
                    mdata.mailbackConnectWith = JQuery('#bfElementCheckboxAdvancedMailbackConnectWith').val();

                    mdata.hint = JQuery('#bfElementTypeCheckboxHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCheckboxHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementCheckboxAdvancedHideLabel').attr('checked');
                    mdata.logging = JQuery('#bfElementCheckboxAdvancedLogging').attr('checked');
                    mdata.orderNumber = JQuery('#bfElementCheckboxAdvancedOrderNumber').val();
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');

                    item.properties = mdata;
                };

                this.populateCheckboxProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementTypeCheckboxValue').val(mdata.value);
                    JQuery('#bfElementTypeCheckboxChecked').attr('checked', mdata.checked);
                    JQuery('#bfElementCheckboxAdvancedMailbackAccept').attr('checked', mdata.mailbackAccept);
                    JQuery('#bfElementCheckboxAdvancedMailbackConnectWith').val(mdata.mailbackConnectWith);
                    JQuery('#bfElementTypeCheckboxReadonly').attr('checked', mdata.readonly);

                    JQuery('#bfElementTypeCheckboxHint').val(mdata.hint);
                    JQuery('#bfElementTypeCheckboxHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementCheckboxAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    JQuery('#bfElementCheckboxAdvancedLogging').attr('checked', mdata.logging);
                    JQuery('#bfElementCheckboxAdvancedOrderNumber').val(mdata.orderNumber);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);
                };

                // Select
                this.saveSelectProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.list = JQuery('#bfElementTypeSelectList').val();
                    mdata['list_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeSelectListTrans').val();

                    mdata.width = JQuery('#bfElementTypeSelectListWidth').val();
                    mdata.height = JQuery('#bfElementTypeSelectListHeight').val();
                    mdata.readonly = JQuery('#bfElementTypeSelectReadonly').attr('checked');
                    mdata.multiple = JQuery('#bfElementTypeSelectMultiple').attr('checked');
                    mdata.mailback = JQuery('#bfElementSelectAdvancedMailback').attr('checked');

                    mdata.hint = JQuery('#bfElementTypeSelectHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeSelectHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementSelectAdvancedHideLabel').attr('checked');
                    mdata.logging = JQuery('#bfElementSelectAdvancedLogging').attr('checked');
                    mdata.orderNumber = JQuery('#bfElementSelectAdvancedOrderNumber').val();
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');

                    item.properties = mdata;
                };

                this.populateSelectProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementTypeSelectList').val(mdata.list);
                    JQuery('#bfElementTypeSelectListTrans').val(typeof mdata['list_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['list_translation<?php echo $active_language_code; ?>'] : "");

                    // compat 723
                    if (typeof mdata.width == "undefined") {
                        mdata['width'] = '';
                    }
                    if (typeof mdata.height == "undefined") {
                        mdata['height'] = '';
                    }
                    // compat 723 end
                    JQuery('#bfElementTypeSelectListWidth').val(mdata.width);
                    JQuery('#bfElementTypeSelectListHeight').val(mdata.height);
                    JQuery('#bfElementTypeSelectReadonly').attr('checked', mdata.readonly);
                    JQuery('#bfElementTypeSelectMultiple').attr('checked', mdata.multiple);
                    JQuery('#bfElementSelectAdvancedMailback').attr('checked', mdata.mailback);

                    JQuery('#bfElementTypeSelectHint').val(mdata.hint);
                    JQuery('#bfElementTypeSelectHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementSelectAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    JQuery('#bfElementSelectAdvancedLogging').attr('checked', mdata.logging);
                    JQuery('#bfElementSelectAdvancedOrderNumber').val(mdata.orderNumber);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);
                };

                // File
                this.saveFileProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.uploadDirectory = JQuery('#bfElementFileAdvancedUploadDirectory').val();
                    mdata.timestamp = JQuery('#bfElementFileAdvancedTimestamp').attr('checked');
                    mdata.allowedFileExtensions = JQuery('#bfElementFileAdvancedAllowedFileExtensions').val();
                    mdata.attachToUserMail = JQuery('#bfElementFileAdvancedAttachToUserMail').attr('checked');
                    mdata.attachToAdminMail = JQuery('#bfElementFileAdvancedAttachToAdminMail').attr('checked');

                    mdata.html5 = JQuery('#bfElementFileAdvancedHtml5Uploader').attr('checked');

                    mdata.readonly = JQuery('#bfElementTypeFileReadonly').attr('checked');

                    mdata.hint = JQuery('#bfElementTypeFileHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeFileHintTrans').val();

                    mdata.useUrl = JQuery('#bfElementFileAdvancedUseUrl').attr('checked');
                    mdata.useUrlDownloadDirectory = JQuery('#bfElementFileAdvancedUseUrlDownloadDirectory').val();

                    mdata.resize_target_width = JQuery('#bfElementFileAdvancedResizeTargetWidth').val();
                    mdata.resize_target_height = JQuery('#bfElementFileAdvancedResizeTargetHeight').val();
                    mdata.resize_type = JQuery('#bfElementFileAdvancedResizeType').val();
                    mdata.resize_bgcolor = JQuery('#bfElementFileAdvancedResizeBgcolor').val();

                    mdata.hideLabel = JQuery('#bfElementFileAdvancedHideLabel').attr('checked');
                    mdata.logging = JQuery('#bfElementFileAdvancedLogging').attr('checked');
                    mdata.orderNumber = JQuery('#bfElementFileAdvancedOrderNumber').val();
                    mdata.flashUploader = JQuery('#bfElementFileAdvancedFlashUploader').attr('checked');
                    mdata.flashUploaderMulti = JQuery('#bfElementFileAdvancedFlashUploaderMulti').attr('checked');
                    mdata.flashUploaderBytes = JQuery('#bfElementFileAdvancedFlashUploaderBytes').val();
                    mdata.flashUploaderWidth = JQuery('#bfElementFileAdvancedFlashUploaderWidth').val();
                    mdata.flashUploaderHeight = JQuery('#bfElementFileAdvancedFlashUploaderHeight').val();
                    mdata.flashUploaderTransparent = JQuery('#bfElementFileAdvancedFlashUploaderTransparent').attr('checked');
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');

                    item.properties = mdata;
                };

                this.populateFileProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementFileAdvancedUploadDirectory').val(mdata.uploadDirectory);
                    JQuery('#bfElementFileAdvancedTimestamp').attr('checked', mdata.timestamp);
                    JQuery('#bfElementFileAdvancedAllowedFileExtensions').val(mdata.allowedFileExtensions);
                    JQuery('#bfElementFileAdvancedAttachToUserMail').attr('checked', mdata.attachToUserMail);
                    JQuery('#bfElementFileAdvancedAttachToAdminMail').attr('checked', mdata.attachToAdminMail);

                    JQuery('#bfElementFileAdvancedHtml5Uploader').attr('checked', mdata.html5);

                    JQuery('#bfElementTypeFileReadonly').attr('checked', mdata.readonly);

                    JQuery('#bfElementTypeFileHint').val(mdata.hint);
                    JQuery('#bfElementTypeFileHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementFileAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    if (mdata.useUrl && mdata.useUrlDownloadDirectory == '') {
                        mdata.useUrlDownloadDirectory = '<?php echo Uri::root() . 'media/breezingforms/uploads'; ?>';
                    }

                    JQuery('#bfElementFileAdvancedResizeTargetWidth').val(mdata.resize_target_width);
                    JQuery('#bfElementFileAdvancedResizeTargetHeight').val(mdata.resize_target_height);
                    JQuery('#bfElementFileAdvancedResizeType').val(mdata.resize_type);
                    JQuery('#bfElementFileAdvancedResizeBgcolor').val(mdata.resize_bgcolor);

                    JQuery('#bfElementFileAdvancedUseUrl').attr('checked', mdata.useUrl);
                    JQuery('#bfElementFileAdvancedUseUrlDownloadDirectory').val(mdata.useUrlDownloadDirectory);
                    JQuery('#bfElementFileAdvancedLogging').attr('checked', mdata.logging);
                    JQuery('#bfElementFileAdvancedOrderNumber').val(mdata.orderNumber);
                    JQuery('#bfElementFileAdvancedFlashUploader').attr('checked', mdata.flashUploader);
                    JQuery('#bfElementFileAdvancedFlashUploaderMulti').attr('checked', mdata.flashUploaderMulti);
                    JQuery('#bfElementFileAdvancedFlashUploaderBytes').val(mdata.flashUploaderBytes);
                    JQuery('#bfElementFileAdvancedFlashUploaderWidth').val(mdata.flashUploaderWidth);
                    JQuery('#bfElementFileAdvancedFlashUploaderHeight').val(mdata.flashUploaderHeight);
                    JQuery('#bfElementFileAdvancedFlashUploaderTransparent').attr('checked', mdata.flashUploaderTransparent);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);
                };

                // SUBMIT BUTTON
                this.saveSubmitButtonProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.src = JQuery('#bfElementSubmitButtonAdvancedSrc').val();
                    mdata['src_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementSubmitButtonAdvancedSrcTrans').val();

                    mdata.value = JQuery('#bfElementTypeSubmitButtonValue').val();
                    mdata['value_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeSubmitButtonValueTrans').val();

                    mdata.hint = JQuery('#bfElementTypeSubmitButtonHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeSubmitButtonHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementSubmitButtonAdvancedHideLabel').attr('checked');
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');

                    item.properties = mdata;
                };

                this.populateSubmitButtonProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementSubmitButtonAdvancedSrc').val(mdata.src);
                    JQuery('#bfElementSubmitButtonAdvancedSrcTrans').val(typeof mdata['src_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['src_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeSubmitButtonValue').val(mdata.value);
                    JQuery('#bfElementTypeSubmitButtonValueTrans').val(typeof mdata['value_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['value_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeSubmitButtonHint').val(mdata.hint);
                    JQuery('#bfElementTypeSubmitButtonHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementSubmitButtonAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                };

                // CAPTCHA
                this.saveCaptchaProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.hint = JQuery('#bfElementTypeCaptchaHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCaptchaHintTrans').val();

                    mdata.width = JQuery('#bfElementTypeCaptchaWidth').val();
                    mdata.hideLabel = JQuery('#bfElementCaptchaAdvancedHideLabel').attr('checked');
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    item.properties = mdata;
                };

                // RECAPTCHA
                this.saveReCaptchaProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.hint = JQuery('#bfElementTypeReCaptchaHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeReCaptchaHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementReCaptchaAdvancedHideLabel').attr('checked');

                    mdata.pubkey = JQuery('#bfElementTypeReCaptchaPubkey').val();
                    mdata.privkey = JQuery('#bfElementTypeReCaptchaPrivkey').val();
                    mdata.theme = JQuery('#bfElementTypeReCaptchaTheme').val();
                    mdata.size = JQuery('#bfElementTypeReCaptchaSize').val();

                    mdata.newCaptcha = JQuery('#bfElementTypeReCaptchaNew').attr('checked');
                    mdata.invisibleCaptcha = JQuery('#bfElementTypeReCaptchaInvisible').attr('checked');

                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();

                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');

                    item.properties = mdata;
                };

                this.populateReCaptchaProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementTypeReCaptchaHint').val(mdata.hint);
                    JQuery('#bfElementTypeReCaptchaHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementReCaptchaAdvancedHideLabel').attr('checked', mdata.hideLabel);

                    JQuery('#bfElementTypeReCaptchaPubkey').val(mdata.pubkey);
                    JQuery('#bfElementTypeReCaptchaPrivkey').val(mdata.privkey);
                    JQuery('#bfElementTypeReCaptchaTheme').val(mdata.theme);
                    JQuery('#bfElementTypeReCaptchaSize').val(mdata.size);

                    JQuery('#bfElementTypeReCaptchaNew').attr('checked', mdata.newCaptcha);
                    JQuery('#bfElementTypeReCaptchaInvisible').attr('checked', mdata.invisibleCaptcha);

                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                };

                this.populateCaptchaProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementTypeCaptchaHint').val(mdata.hint);
                    JQuery('#bfElementTypeCaptchaHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeCaptchaWidth').val(mdata.width);
                    JQuery('#bfElementCaptchaAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                };

                // CALENDAR RESPONSIVE
                this.saveCalendarResponsiveProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.format = JQuery('#bfElementTypeCalendarResponsiveFormat').val();
                    mdata['format_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCalendarResponsiveFormatTrans').val();

                    mdata.value = JQuery('#bfElementTypeCalendarResponsiveValue').val();
                    mdata['value_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCalendarResponsiveValueTrans').val();

                    mdata.size = JQuery('#bfElementTypeCalendarResponsiveSize').val();
                    mdata.icon = JQuery('#bfElementTypeCalendarResponsiveIcon').val();

                    mdata.hint = JQuery('#bfElementTypeCalendarResponsiveHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCalendarResponsiveHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementCalendarResponsiveAdvancedHideLabel').attr('checked');
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');

                    item.properties = mdata;
                };

                this.populateCalendarResponsiveProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementTypeCalendarResponsiveFormat').val(mdata.format);
                    JQuery('#bfElementTypeCalendarResponsiveFormatTrans').val(typeof mdata['format_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['format_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeCalendarResponsiveValue').val(mdata.value);
                    JQuery('#bfElementTypeCalendarResponsiveValueTrans').val(typeof mdata['value_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['value_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeCalendarResponsiveSize').val(mdata.size);

                    JQuery('#bfElementTypeCalendarResponsiveHint').val(mdata.hint);
                    JQuery('#bfElementTypeCalendarResponsiveHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementCalendarResponsiveAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);

                    JQuery('#bfElementTypeCalendarResponsiveIcon').val(mdata.icon);
                };

                // CALENDAR
                this.saveCalendarProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.format = JQuery('#bfElementTypeCalendarFormat').val();
                    mdata['format_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCalendarFormatTrans').val();

                    mdata.value = JQuery('#bfElementTypeCalendarValue').val();
                    mdata['value_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCalendarValueTrans').val();

                    mdata.size = JQuery('#bfElementTypeCalendarSize').val();
                    mdata.icon = JQuery('#bfElementTypeCalendarIcon').val();

                    mdata.hint = JQuery('#bfElementTypeCalendarHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeCalendarHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementCalendarAdvancedHideLabel').attr('checked');
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    mdata.required = JQuery('#bfElementValidationRequired').attr('checked');

                    /* versions > 3.7 */
                    mdata.showTime = JQuery('#bfElementCalendarAdvancedShowTime').attr('checked');
                    mdata.timeFormat = JQuery('#bfElementCalendarAdvancedTimeFormat').attr('checked');
                    mdata.singleHeader = JQuery('#bfElementCalendarAdvancedSingleHeader').attr('checked');
                    mdata.todayButton = JQuery('#bfElementCalendarAdvancedTodayButton').attr('checked');
                    mdata.weekNumbers = JQuery('#bfElementCalendarAdvancedWeekNumbers').attr('checked');
                    mdata.minYear = JQuery('#bfElementCalendarAdvancedMinYear').val();
                    mdata.maxYear = JQuery('#bfElementCalendarAdvancedMaxYear').val();
                    mdata.firstDay = JQuery('#bfElementCalendarAdvancedFirstDay').val();

                    item.properties = mdata;
                };

                this.populateCalendarProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementTypeCalendarFormat').val(mdata.format);
                    JQuery('#bfElementTypeCalendarFormatTrans').val(typeof mdata['format_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['format_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeCalendarValue').val(mdata.value);
                    JQuery('#bfElementTypeCalendarValueTrans').val(typeof mdata['value_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['value_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeCalendarSize').val(mdata.size);

                    JQuery('#bfElementTypeCalendarHint').val(mdata.hint);
                    JQuery('#bfElementTypeCalendarHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementCalendarAdvancedHideLabel').attr('checked', mdata.hideLabel);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementValidationRequired').attr('checked', mdata.required);

                    JQuery('#bfElementTypeCalendarIcon').val(mdata.icon);

                    /* > 3.7 */
                    JQuery('#bfElementCalendarAdvancedShowTime').attr('checked', mdata.showTime);
                    JQuery('#bfElementCalendarAdvancedTimeFormat').attr('checked', mdata.timeFormat);
                    JQuery('#bfElementCalendarAdvancedSingleHeader').attr('checked', mdata.singleHeader);
                    JQuery('#bfElementCalendarAdvancedTodayButton').attr('checked', mdata.todayButton);
                    JQuery('#bfElementCalendarAdvancedWeekNumbers').attr('checked', mdata.weekNumbers);
                    JQuery('#bfElementCalendarAdvancedMinYear').val(mdata.minYear);
                    JQuery('#bfElementCalendarAdvancedMaxYear').val(mdata.maxYear);
                    JQuery('#bfElementCalendarAdvancedFirstDay').val(mdata.firstDay);
                };

                // Hidden
                this.saveHiddenProperties = function (mdata, item) {
                    // dynamic properties
                    mdata.value = JQuery('#bfElementTypeHiddenValue').val();
                    mdata.logging = JQuery('#bfElementHiddenAdvancedLogging').attr('checked');
                    mdata.orderNumber = JQuery('#bfElementHiddenAdvancedOrderNumber').val();
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');

                    item.properties = mdata;
                };

                this.populateHiddenProperties = function (mdata) {
                    // dynamic properties
                    JQuery('#bfElementTypeHiddenValue').val(mdata.value);
                    JQuery('#bfElementHiddenAdvancedLogging').attr('checked', mdata.logging);
                    JQuery('#bfElementHiddenAdvancedOrderNumber').val(mdata.orderNumber);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                };

                // SUMMARIZE
                this.saveSummarizeProperties = function (mdata, item) {
                    // dynamic properties
                    var val = JQuery('#bfElementTypeSummarizeConnectWith').val();
                    if (val != '') {
                        var name = val.split(":")[0];
                        var type = val.split(":")[1];
                        mdata.connectWith = name;
                        mdata.connectType = type;
                    }

                    mdata.useElementLabel = JQuery('#bfElementTypeSummarizeUseElementLabel').attr('checked');
                    mdata.hideIfEmpty = JQuery('#bfElementTypeSummarizeHideIfEmpty').attr('checked');
                    mdata.fieldCalc = JQuery('#bfElementAdvancedSummarizeCalc').val();

                    mdata.emptyMessage = JQuery('#bfElementTypeSummarizeEmptyMessage').val();
                    mdata['emptyMessage_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeSummarizeEmptyMessageTrans').val();

                    if (mdata.useElementLabel) {
                        var items = new Array();
                        appScope.getItemsFlattened(appScope.dataObject, items);
                        for (var i = 0; i < items.length; i++) {
                            if (items[i].properties.bfName == name) {
                                JQuery('#bfElementLabel').val(items[i].properties.label);
                                JQuery('#bfElementLabelTrans').val(typeof items[i].properties['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? items[i].properties['label_translation<?php echo $active_language_code; ?>'] : "");
                                break;
                            }
                        }
                    }
                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    item.properties = mdata;
                };

                this.populateSummarizeProperties = function (mdata) {
                    var items = new Array();
                    appScope.getItemsFlattened(appScope.dataObject, items);
                    JQuery('#bfElementTypeSummarizeConnectWith').empty();
                    var option = document.createElement('option');
                    JQuery(option).val('');
                    JQuery(option).text("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_CHOOSE_ONE')); ?>");
                    JQuery('#bfElementTypeSummarizeConnectWith').append(option);
                    for (var i = 0; i < items.length; i++) {
                        switch (items[i].properties.bfType) {
                            case 'bfTextfield':
                            case 'bfTextarea':
                            case 'bfRadioGroup':
                            case 'bfCheckboxGroup':
                            case 'bfCheckbox':
                            case 'bfSelect':
                            case 'bfFile':
                            case 'bfHidden':
                            case 'bfCalendar':
                            case 'bfNumberInput':
                            case 'bfCalendarResponsive':
                                var option = document.createElement('option');
                                JQuery(option).val(items[i].properties.bfName + ":" + items[i].properties.bfType);
                                JQuery(option).text(items[i].properties.label + " (" + items[i].properties.bfName + ")");
                                JQuery('#bfElementTypeSummarizeConnectWith').append(option);
                                break;
                        }
                    }
                    // dynamic properties
                    JQuery('#bfElementTypeSummarizeConnectWith').val(mdata.connectWith + ":" + mdata.connectType);
                    JQuery('#bfElementTypeSummarizeEmptyMesssage').val(mdata.emptyMessage);
                    JQuery('#bfElementTypeSummarizeUseElementLabel').attr('checked', mdata.useElementLabel);

                    JQuery('#bfElementTypeSummarizeEmptyMessage').val(mdata.emptyMessage);
                    JQuery('#bfElementTypeSummarizeEmptyMessageTrans').val(typeof mdata['emptyMessage_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['emptyMessage_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementTypeSummarizeHideIfEmpty').attr('checked', mdata.hideIfEmpty);
                    JQuery('#bfElementAdvancedSummarizeCalc').val(mdata.fieldCalc);
                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                };



                // STRIPE BUTTON
                this.saveStripeProperties = function (mdata, item) {
                    // dynamic properties

                    // DEFAULT

                    // account
                    mdata.secretKey = JQuery('#bfElementTypeStripeSecretKey').val();
                    mdata.publishableKey = JQuery('#bfElementTypeStripePublishableKey').val();
                    mdata.itemname = JQuery('#bfElementTypeStripeItemname').val();
                    mdata.amount = JQuery('#bfElementTypeStripeAmount').val();
                    mdata.thankYouPage = JQuery('#bfElementTypeStripeThankYouPage').val();

                    mdata.currencyCode = JQuery('#bfElementTypeStripeCurrencyCode').val();
                    mdata.sendNotificationAfterPayment = JQuery('#bfElementTypeStripeSendNotificationAfterPayment').attr('checked');

                    // ADVANCED

                    mdata.image = JQuery('#bfElementStripeAdvancedImage').val();
                    mdata['image_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementStripeAdvancedImageTrans').val();


                    // file
                    mdata.downloadableFile = JQuery('#bfElementStripeAdvancedDownloadableFile').attr('checked');
                    mdata.filepath = JQuery('#bfElementStripeAdvancedFilepath').val();
                    mdata.downloadTries = JQuery('#bfElementStripeAdvancedDownloadTries').val();

                    // OTHER ADVANCED
                    mdata.hint = JQuery('#bfElementTypeStripeHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeStripeHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementStripeAdvancedHideLabel').attr('checked');
                    mdata.emailfield = JQuery('#bfElementStripeAdvancedEmailField').val();

                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    item.properties = mdata;
                };

                this.populateStripeProperties = function (mdata) {
                    // dynamic properties

                    // DEFAULT

                    // account
                    JQuery('#bfElementTypeStripeSecretKey').val(mdata.secretKey);
                    JQuery('#bfElementTypeStripePublishableKey').val(mdata.publishableKey);

                    JQuery('#bfElementTypeStripeItemname').val(mdata.itemname);
                    JQuery('#bfElementTypeStripeAmount').val(mdata.amount);
                    JQuery('#bfElementTypeStripeThankYouPage').val(mdata.thankYouPage);
                    JQuery('#bfElementTypeStripeCurrencyCode').val(mdata.currencyCode);
                    JQuery('#bfElementTypeStripeSendNotificationAfterPayment').attr('checked', mdata.sendNotificationAfterPayment);
                    // ADVANCED

                    JQuery('#bfElementStripeAdvancedImage').val(mdata.image);
                    JQuery('#bfElementStripeAdvancedImageTrans').val(typeof mdata['image_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['image_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementStripeAdvancedEmailField').val(mdata.emailfield);

                    // file
                    JQuery('#bfElementStripeAdvancedDownloadableFile').attr('checked', mdata.downloadableFile);
                    JQuery('#bfElementStripeAdvancedFilepath').val(mdata.filepath);
                    JQuery('#bfElementStripeAdvancedDownloadTries').val(mdata.downloadTries);

                    JQuery('#bfElementTypeStripeHint').val(mdata.hint);
                    JQuery('#bfElementTypeStripeHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementStripeAdvancedHideLabel').attr('checked', mdata.hideLabel);

                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                };


                // PAYPAL BUTTON
                this.savePayPalProperties = function (mdata, item) {
                    // dynamic properties

                    // DEFAULT

                    // account
                    mdata.business = JQuery('#bfElementTypePayPalBusiness').val();
                    mdata.token = JQuery('#bfElementTypePayPalToken').val();
                    mdata.cancelURL = JQuery('#bfElementTypePayPalCancelURL').val();
                    mdata.itemname = JQuery('#bfElementTypePayPalItemname').val();
                    mdata.itemnumber = JQuery('#bfElementTypePayPalItemnumber').val();
                    mdata.amount = JQuery('#bfElementTypePayPalAmount').val();
                    mdata.tax = JQuery('#bfElementTypePayPalTax').val();
                    mdata.thankYouPage = JQuery('#bfElementTypePayPalThankYouPage').val();
                    mdata.locale = JQuery('#bfElementTypePayPalLocale').val();
                    mdata.currencyCode = JQuery('#bfElementTypePayPalCurrencyCode').val();
                    mdata.sendNotificationAfterPayment = JQuery('#bfElementTypePayPalSendNotificationAfterPayment').attr('checked');

                    // ADVANCED

                    mdata.useIpn = JQuery('#bfElementPayPalAdvancedUseIpn').attr('checked');

                    mdata.image = JQuery('#bfElementPayPalAdvancedImage').val();
                    mdata['image_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementPayPalAdvancedImageTrans').val();

                    // testaccount
                    mdata.testaccount = JQuery('#bfElementPayPalAdvancedTestaccount').attr('checked');
                    mdata.testBusiness = JQuery('#bfElementPayPalAdvancedTestBusiness').val();
                    mdata.testToken = JQuery('#bfElementPayPalAdvancedTestToken').val();

                    // file
                    mdata.downloadableFile = JQuery('#bfElementPayPalAdvancedDownloadableFile').attr('checked');
                    mdata.filepath = JQuery('#bfElementPayPalAdvancedFilepath').val();
                    mdata.downloadTries = JQuery('#bfElementPayPalAdvancedDownloadTries').val();

                    // OTHER ADVANCED
                    mdata.hint = JQuery('#bfElementTypePayPalHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypePayPalHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementPayPalAdvancedHideLabel').attr('checked');

                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    item.properties = mdata;
                };

                this.populatePayPalProperties = function (mdata) {
                    // dynamic properties

                    // DEFAULT

                    // account
                    JQuery('#bfElementTypePayPalBusiness').val(mdata.business);
                    JQuery('#bfElementTypePayPalToken').val(mdata.token);
                    JQuery('#bfElementTypePayPalCancelURL').val(mdata.cancelURL);
                    JQuery('#bfElementTypePayPalItemname').val(mdata.itemname);
                    JQuery('#bfElementTypePayPalItemnumber').val(mdata.itemnumber);
                    JQuery('#bfElementTypePayPalAmount').val(mdata.amount);
                    JQuery('#bfElementTypePayPalTax').val(mdata.tax);
                    JQuery('#bfElementTypePayPalThankYouPage').val(mdata.thankYouPage);
                    JQuery('#bfElementTypePayPalLocale').val(mdata.locale);
                    JQuery('#bfElementTypePayPalCurrencyCode').val(mdata.currencyCode);
                    JQuery('#bfElementTypePayPalSendNotificationAfterPayment').attr('checked', mdata.sendNotificationAfterPayment);
                    // ADVANCED

                    JQuery('#bfElementPayPalAdvancedImage').val(mdata.image);
                    JQuery('#bfElementPayPalAdvancedImageTrans').val(typeof mdata['image_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['image_translation<?php echo $active_language_code; ?>'] : "");


                    // testaccount
                    JQuery('#bfElementPayPalAdvancedTestaccount').attr('checked', mdata.testaccount);
                    JQuery('#bfElementPayPalAdvancedTestBusiness').val(mdata.testBusiness);
                    JQuery('#bfElementPayPalAdvancedTestToken').val(mdata.testToken);

                    // file
                    JQuery('#bfElementPayPalAdvancedDownloadableFile').attr('checked', mdata.downloadableFile);
                    JQuery('#bfElementPayPalAdvancedFilepath').val(mdata.filepath);
                    JQuery('#bfElementPayPalAdvancedDownloadTries').val(mdata.downloadTries);
                    if (typeof mdata.useIpn == "undefined") {
                        mdata['useIpn'] = false;
                    }
                    JQuery('#bfElementPayPalAdvancedUseIpn').attr('checked', mdata.useIpn);

                    JQuery('#bfElementTypePayPalHint').val(mdata.hint);
                    JQuery('#bfElementTypePayPalHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementPayPalAdvancedHideLabel').attr('checked', mdata.hideLabel);

                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                };

                // SOFORTUEBERWEISUNG BUTTON
                this.saveSofortueberweisungProperties = function (mdata, item) {
                    // dynamic properties

                    // DEFAULT

                    // account
                    mdata.user_id = JQuery('#bfElementTypeSofortueberweisungUserId').val();
                    mdata.project_id = JQuery('#bfElementTypeSofortueberweisungProjectId').val();
                    mdata.project_password = JQuery('#bfElementTypeSofortueberweisungProjectPassword').val();

                    mdata.reason_1 = JQuery('#bfElementTypeSofortueberweisungReason1').val();
                    mdata.reason_2 = JQuery('#bfElementTypeSofortueberweisungReason2').val();
                    mdata.amount = JQuery('#bfElementTypeSofortueberweisungAmount').val();
                    mdata.thankYouPage = JQuery('#bfElementTypeSofortueberweisungThankYouPage').val();
                    mdata.language_id = JQuery('#bfElementTypeSofortueberweisungLanguageId').val();
                    mdata.currency_id = JQuery('#bfElementTypeSofortueberweisungCurrencyId').val();
                    mdata.mailback = JQuery('#bfElementTypeSofortueberweisungMailback').attr('checked');
                    mdata.sendNotificationAfterPayment = JQuery('#bfElementTypeSofortueberweisungSendNotificationAfterPayment').attr('checked');

                    // ADVANCED

                    mdata.image = JQuery('#bfElementSofortueberweisungAdvancedImage').val();
                    mdata['image_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementSofortueberweisungAdvancedImageTrans').val();

                    // file
                    mdata.downloadableFile = JQuery('#bfElementSofortueberweisungAdvancedDownloadableFile').attr('checked');
                    mdata.filepath = JQuery('#bfElementSofortueberweisungAdvancedFilepath').val();
                    mdata.downloadTries = JQuery('#bfElementSofortueberweisungAdvancedDownloadTries').val();

                    // OTHER ADVANCED
                    mdata.hint = JQuery('#bfElementTypeSofortueberweisungHint').val();
                    mdata['hint_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementTypeSofortueberweisungHintTrans').val();

                    mdata.hideLabel = JQuery('#bfElementSofortueberweisungAdvancedHideLabel').attr('checked');

                    // static properties
                    mdata.bfName = JQuery('#bfElementName').val();

                    mdata.label = JQuery('#bfElementLabel').val();
                    mdata['label_translation<?php echo $active_language_code; ?>'] = JQuery('#bfElementLabelTrans').val();

                    mdata.labelPosition = JQuery('#bfElementAdvancedLabelPosition').val();
                    mdata.tabIndex = JQuery('#bfElementAdvancedTabIndex').val();
                    mdata.hideInMailback = JQuery('#bfElementAdvancedHideInMailback').attr('checked');
                    mdata.off = JQuery('#bfElementAdvancedTurnOff').attr('checked');
                    item.properties = mdata;
                };

                this.populateSofortueberweisungProperties = function (mdata) {
                    // dynamic properties

                    // DEFAULT

                    // account
                    JQuery('#bfElementTypeSofortueberweisungUserId').val(mdata.user_id);
                    JQuery('#bfElementTypeSofortueberweisungProjectId').val(mdata.project_id);
                    JQuery('#bfElementTypeSofortueberweisungProjectPassword').val(mdata.project_password);

                    JQuery('#bfElementTypeSofortueberweisungReason1').val(mdata.reason_1);
                    JQuery('#bfElementTypeSofortueberweisungReason2').val(mdata.reason_2);
                    JQuery('#bfElementTypeSofortueberweisungAmount').val(mdata.amount);
                    JQuery('#bfElementTypeSofortueberweisungThankYouPage').val(mdata.thankYouPage);
                    JQuery('#bfElementTypeSofortueberweisungLanguageId').val(mdata.language_id);
                    JQuery('#bfElementTypeSofortueberweisungCurrencyId').val(mdata.currency_id);
                    JQuery('#bfElementTypeSofortueberweisungMailback').attr('checked', mdata.mailback);
                    JQuery('#bfElementTypeSofortueberweisungSendNotificationAfterPayment').attr('checked', mdata.sendNotificationAfterPayment);

                    // ADVANCED

                    JQuery('#bfElementSofortueberweisungAdvancedImage').val(mdata.image);
                    JQuery('#bfElementSofortueberweisungAdvancedImageTrans').val(typeof mdata['image_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['image_translation<?php echo $active_language_code; ?>'] : "");

                    // file
                    JQuery('#bfElementSofortueberweisungAdvancedDownloadableFile').attr('checked', mdata.downloadableFile);
                    JQuery('#bfElementSofortueberweisungAdvancedFilepath').val(mdata.filepath);
                    JQuery('#bfElementSofortueberweisungAdvancedDownloadTries').val(mdata.downloadTries);

                    // OTHER ADVANCED
                    JQuery('#bfElementTypeSofortueberweisungHint').val(mdata.hint);
                    JQuery('#bfElementTypeSofortueberweisungHintTrans').val(typeof mdata['hint_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['hint_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementSofortueberweisungAdvancedHideLabel').attr('checked', mdata.hideLabel);

                    // static properties
                    JQuery('#bfElementName').val(mdata.bfName);

                    JQuery('#bfElementLabel').val(mdata.label);
                    JQuery('#bfElementLabelTrans').val(typeof mdata['label_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['label_translation<?php echo $active_language_code; ?>'] : "");

                    JQuery('#bfElementAdvancedTabIndex').val(mdata.tabIndex);
                    JQuery('#bfElementAdvancedHideInMailback').attr('checked', mdata.hideInMailback);
                    JQuery('#bfElementAdvancedTurnOff').attr('checked', mdata.off);
                    JQuery('#bfElementAdvancedLabelPosition').val(mdata.labelPosition);
                };

                this.saveSelectedElementProperties = function () {
                    if (appScope.selectedTreeElement) {
                        var mdata = appScope.getProperties(appScope.selectedTreeElement);
                        if (mdata) {
                            var item = appScope.findDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), appScope.dataObject);
                            if (item) {

                                switch (mdata.bfType) {
                                    case 'bfSummarize':
                                        appScope.saveSummarizeProperties(mdata, item);
                                        break;
                                    case 'bfHidden':
                                        appScope.saveHiddenProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        break;
                                    case 'bfTextfield':
                                        appScope.saveTextProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfTextarea':
                                        appScope.saveTextareaProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfRadioGroup':
                                        appScope.saveRadioGroupProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfSubmitButton':
                                        appScope.saveSubmitButtonProperties(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfStripe':
                                        appScope.saveStripeProperties(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfSignature':
                                        appScope.saveSignatureProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfPayPal':
                                        appScope.savePayPalProperties(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfSofortueberweisung':
                                        appScope.saveSofortueberweisungProperties(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfCaptcha':
                                        appScope.saveCaptchaProperties(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfReCaptcha':
                                        appScope.saveReCaptchaProperties(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfCalendar':
                                        appScope.saveCalendarProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        break;
                                    case 'bfCalendarResponsive':
                                        appScope.saveCalendarResponsiveProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        break;
                                    case 'bfCheckboxGroup':
                                        appScope.saveCheckboxGroupProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfCheckbox':
                                        appScope.saveCheckboxProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfSelect':
                                        appScope.saveSelectProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfFile':
                                        appScope.saveFileProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                    case 'bfNumberInput':
                                        appScope.saveNumberInputProperties(mdata, item);
                                        appScope.saveValidation(mdata, item);
                                        appScope.saveInit(mdata, item);
                                        appScope.saveAction(mdata, item);
                                        break;
                                }
                                item.attributes.id = JQuery('#bfElementName').val();
                                JQuery(appScope.selectedTreeElement).attr('id', JQuery('#bfElementName').val());
                            }
                        }
                    }
                };

                this.saveValidation = function (mdata, item) {
                    mdata.validationId = JQuery('#bfValidationScriptSelection').val();
                    mdata.validationCode = Joomla.editors.instances["bfValidationCode"].getValue();
                    mdata.validationMessage = JQuery('#bfValidationMessage').val();
                    mdata['validationMessage_translation<?php echo $active_language_code; ?>'] = JQuery('#bfValidationMessageTrans').val();

                    if (JQuery('#bfValidationTypeLibrary').get(0).checked) {
                        mdata.validationCondition = 1;
                        for (var i = 0; i < appScope.elementScripts.validation.length; i++) {
                            if (appScope.elementScripts.validation[i].id == JQuery('#bfValidationScriptSelection').val()) {
                                mdata.validationFunctionName = appScope.elementScripts.validation[i].name;
                                break;
                            }
                        }

                    } else if (JQuery('#bfValidationTypeCustom').get(0).checked) {
                        mdata.validationCondition = 2;
                        mdata.validationFunctionName = 'ff_' + mdata.bfName + '_validation';
                    } else {
                        mdata.validationCondition = 0;
                    }
                    item.properties = mdata;
                };

                this.saveInit = function (mdata, item) {
                    if (JQuery('#bfInitFormEntry').get(0).checked) {
                        mdata.initFormEntry = 1;
                    } else {
                        mdata.initFormEntry = 0;
                    }

                    if (JQuery('#bfInitPageEntry').get(0).checked) {
                        mdata.initPageEntry = 1;
                    } else {
                        mdata.initPageEntry = 0;
                    }

                    mdata.initId = JQuery('#bfInitScriptSelection').val();
                    mdata.initCode = Joomla.editors.instances["bfInitCode"].getValue();

                    if (JQuery('#bfInitTypeLibrary').get(0).checked) {
                        mdata.initCondition = 1;
                        for (var i = 0; i < appScope.elementScripts.init.length; i++) {
                            if (appScope.elementScripts.init[i].id == JQuery('#bfInitScriptSelection').val()) {
                                mdata.initScript = appScope.elementScripts.init[i].name;
                                break;
                            }
                        }

                    } else if (JQuery('#bfInitTypeCustom').get(0).checked) {
                        mdata.initCondition = 2;
                        mdata.initFunctionName = 'ff_' + mdata.bfName + '_init';
                    } else {
                        mdata.initCondition = 0;
                    }
                    item.properties = mdata;
                };

                this.saveAction = function (mdata, item) {

                    mdata.actionId = JQuery('#bfActionsScriptSelection').val();
                    mdata.actionCode = Joomla.editors.instances["bfActionCode"].getValue();

                    if (JQuery('#bfActionTypeLibrary').get(0).checked) {
                        mdata.actionCondition = 1;
                        for (var i = 0; i < appScope.elementScripts.action.length; i++) {
                            if (appScope.elementScripts.action[i].id == JQuery('#bfActionsScriptSelection').val()) {
                                mdata.actionFunctionName = appScope.elementScripts.action[i].name;
                                break;
                            }
                        }
                    } else if (JQuery('#bfActionTypeCustom').get(0).checked) {
                        mdata.actionCondition = 2;
                        mdata.actionFunctionName = 'ff_' + mdata.bfName + '_action';
                    } else {
                        mdata.actionCondition = 0;
                    }

                    if (JQuery('#bfActionClick').get(0).checked && mdata.actionCondition > 0) {
                        mdata.actionClick = 1;
                    } else {
                        mdata.actionClick = 0;
                    }

                    if (JQuery('#bfActionBlur').get(0).checked && mdata.actionCondition > 0) {
                        mdata.actionBlur = 1;
                    } else {
                        mdata.actionBlur = 0;
                    }

                    if (JQuery('#bfActionChange').get(0).checked && mdata.actionCondition > 0) {
                        mdata.actionChange = 1;
                    } else {
                        mdata.actionChange = 0;
                    }

                    if (JQuery('#bfActionFocus').get(0).checked && mdata.actionCondition > 0) {
                        mdata.actionFocus = 1;
                    } else {
                        mdata.actionFocus = 0;
                    }

                    if (JQuery('#bfActionSelect').get(0).checked && mdata.actionCondition > 0) {
                        mdata.actionSelect = 1;
                    } else {
                        mdata.actionSelect = 0;
                    }

                    item.properties = mdata;
                };

                this.populateSelectedElementProperties = function () {
                    if (appScope.selectedTreeElement) {
                        var mdata = appScope.getProperties(appScope.selectedTreeElement);

                        // compat 723
                        if (typeof mdata.off == "undefined") {
                            mdata['off'] = false;
                        }
                        // compat 723 end

                        if (mdata) {
                            var item = appScope.findDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), appScope.dataObject);
                            if (item) {
                                item.data.title = JQuery("<div/>").text(mdata.label).html();
                                JQuery('#bfValidationScript').css('display', 'none');
                                JQuery('#bfInitScript').css('display', 'none');
                                JQuery('#bfActionScript').css('display', 'none');

                                JQuery('#bfElementTypeText').css('display', 'none');
                                JQuery('#bfElementTypeTextarea').css('display', 'none');
                                JQuery('#bfElementTypeRadioGroup').css('display', 'none');
                                JQuery('#bfElementTypeSubmitButton').css('display', 'none');
                                JQuery('#bfElementTypeStripe').css('display', 'none');
                                JQuery('#bfElementTypeSignature').css('display', 'none');
                                JQuery('#bfElementTypePayPal').css('display', 'none');
                                JQuery('#bfElementTypeSofortueberweisung').css('display', 'none');
                                JQuery('#bfElementTypeCaptcha').css('display', 'none');
                                JQuery('#bfElementTypeReCaptcha').css('display', 'none');
                                JQuery('#bfElementTypeCalendar').css('display', 'none');
                                JQuery('#bfElementTypeCalendarResponsive').css('display', 'none');
                                JQuery('#bfElementTypeCheckboxGroup').css('display', 'none');
                                JQuery('#bfElementTypeCheckbox').css('display', 'none');
                                JQuery('#bfElementTypeSelect').css('display', 'none');
                                JQuery('#bfElementTypeFile').css('display', 'none');
                                JQuery('#bfElementTypeHidden').css('display', 'none');
                                JQuery('#bfElementTypeSummarize').css('display', 'none');
                                JQuery('#bfElementTypeNumberInput').css('display', 'none');

                                JQuery('#bfElementTypeTextAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeTextareaAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeRadioGroupAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeSubmitButtonAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeStripeAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeSignatureAdvanced').css('display', 'none');
                                JQuery('#bfElementTypePayPalAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeSofortueberweisungAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeCaptchaAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeReCaptchaAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeCalendarAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeCalendarResponsiveAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeCheckboxGroupAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeCheckboxAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeSelectAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeFileAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeHiddenAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeSummarizeAdvanced').css('display', 'none');
                                JQuery('#bfElementTypeNumberInputAdvanced').css('display', 'none');
                                JQuery('#bfElementValidationRequiredSet').css('display', 'none');

                                JQuery('#bfAdvancedLeaf').css('display', '');
                                JQuery('#bfHideInMailback').css('display', '');

                                switch (mdata.bfType) {
                                    case 'bfNumberInput':
                                        JQuery('#bfElementType').val('bfElementTypeNumberInput');
                                        appScope.populateNumberInputProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfSummarize':
                                        JQuery('#bfHideInMailback').css('display', 'none');
                                        JQuery('#bfElementType').val('bfElementTypeSummarize');
                                        appScope.populateSummarizeProperties(mdata);
                                        break;
                                    case 'bfHidden':
                                        JQuery('#bfElementType').val('bfElementTypeHidden');
                                        JQuery('#bfAdvancedLeaf').css('display', 'none');
                                        appScope.populateHiddenProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        break;
                                    case 'bfTextfield':
                                        JQuery('#bfElementType').val('bfElementTypeText');
                                        appScope.populateTextProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfTextarea':
                                        JQuery('#bfElementType').val('bfElementTypeTextarea');
                                        appScope.populateTextareaProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfRadioGroup':
                                        JQuery('#bfElementType').val('bfElementTypeRadioGroup');
                                        appScope.populateRadioGroupProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfSubmitButton':
                                        JQuery('#bfElementType').val('bfElementTypeSubmitButton');
                                        appScope.populateSubmitButtonProperties(mdata);
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfStripe':
                                        JQuery('#bfElementType').val('bfElementTypeStripe');
                                        appScope.populateStripeProperties(mdata);
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfSignature':
                                        JQuery('#bfElementType').val('bfElementTypeSignature');
                                        appScope.populateSignatureProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfPayPal':
                                        JQuery('#bfElementType').val('bfElementTypePayPal');
                                        appScope.populatePayPalProperties(mdata);
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfSofortueberweisung':
                                        JQuery('#bfElementType').val('bfElementTypeSofortueberweisung');
                                        appScope.populateSofortueberweisungProperties(mdata);
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfCaptcha':
                                        JQuery('#bfHideInMailback').css('display', 'none');
                                        JQuery('#bfElementType').val('bfElementTypeCaptcha');
                                        appScope.populateCaptchaProperties(mdata);
                                        break;
                                    case 'bfReCaptcha':
                                        JQuery('#bfHideInMailback').css('display', 'none');
                                        JQuery('#bfElementType').val('bfElementTypeReCaptcha');
                                        appScope.populateReCaptchaProperties(mdata);
                                        break;
                                    case 'bfCalendar':
                                        JQuery('#bfElementType').val('bfElementTypeCalendar');
                                        appScope.populateCalendarProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        break;
                                    case 'bfCalendarResponsive':
                                        JQuery('#bfElementType').val('bfElementTypeCalendarResponsive');
                                        appScope.populateCalendarResponsiveProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        break;
                                    case 'bfCheckboxGroup':
                                        JQuery('#bfElementType').val('bfElementTypeCheckboxGroup');
                                        appScope.populateCheckboxGroupProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfCheckbox':
                                        JQuery('#bfElementType').val('bfElementTypeCheckbox');
                                        appScope.populateCheckboxProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfSelect':
                                        JQuery('#bfElementType').val('bfElementTypeSelect');
                                        appScope.populateSelectProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        appScope.populateElementActionScript();
                                        break;
                                    case 'bfFile':
                                        JQuery('#bfElementType').val('bfElementTypeFile');
                                        appScope.populateFileProperties(mdata);
                                        appScope.populateElementValidationScript();
                                        appScope.populateElementInitScript();
                                        appScope.populateElementActionScript();
                                        break;
                                }

                                if (JQuery('#bfElementType').val() != '') {
                                    JQuery('#bfElementTypeClass').css('display', 'none');
                                    JQuery('#' + JQuery('#bfElementType').val()).css('display', '');
                                    JQuery('#' + JQuery('#bfElementType').val() + "Advanced").css('display', '');
                                    if (mdata.bfType != 'bfHidden') {
                                        JQuery('#bfElementValidationRequiredSet').css('display', '');
                                    }
                                }
                            }
                        }
                    }
                };

                this.populateElementValidationScript = function () {

                    var mdata = appScope.getProperties(appScope.selectedTreeElement);
                    if (mdata) {

                        JQuery('#bfValidationScript').css('display', '');

                        JQuery('#bfValidationScriptSelection').empty();
                        for (var i = 0; i < appScope.elementScripts.validation.length; i++) {
                            var option = document.createElement('option');
                            JQuery(option).val(appScope.elementScripts.validation[i].id);
                            JQuery(option).text(appScope.elementScripts.validation[i].package + '::' + appScope.elementScripts.validation[i].name);
                            if (appScope.elementScripts.validation[i].id == mdata.validationId) {
                                JQuery(option).get(0).setAttribute('selected', true);
                            }
                            JQuery('#bfValidationScriptSelection').append(option);
                        }

                        JQuery('#bfValidationMessage').val(mdata.validationMessage);
                        JQuery('#bfValidationMessageTrans').val(typeof mdata['validationMessage_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['validationMessage_translation<?php echo $active_language_code; ?>'] : "");

                        Joomla.editors.instances["bfValidationCode"].setValue(mdata.validationCode);

                        switch (mdata.validationCondition) {
                            case 1:
                                JQuery('.bfValidationType').attr('checked', '');
                                JQuery('#bfValidationTypeLibrary').attr('checked', true);
                                JQuery('#bfValidationScriptLibrary').css('display', '');
                                JQuery('#bfValidationScriptCustom').css('display', 'none');
                                JQuery('#bfValidationScriptFlags').css('display', '');
                                JQuery('#bfValidationScriptLibrary').css('display', '');
                                JQuery('#bfValidationScriptCustom').css('display', 'none');
                                appScope.setValidationScriptDescription();
                                break;
                            case 2:
                                JQuery('.bfValidationType').attr('checked', '');
                                JQuery('#bfValidationTypeCustom').attr('checked', true);
                                JQuery('#bfValidationScriptFlags').css('display', '');
                                JQuery('#bfValidationScriptLibrary').css('display', 'none');
                                JQuery('#bfValidationScriptCustom').css('display', '');
                                break;
                            default:
                                JQuery('.bfValidationType').attr('checked', '');
                                JQuery('#bfValidationTypeNone').attr('checked', true);
                                JQuery('#bfValidationScriptFlags').css('display', 'none');
                                JQuery('#bfValidationScriptLibrary').css('display', 'none');
                                JQuery('#bfValidationScriptCustom').css('display', 'none');
                        }
                    }

                };

                this.populateElementInitScript = function () {

                    var mdata = appScope.getProperties(appScope.selectedTreeElement);
                    if (mdata) {

                        JQuery('#bfInitScript').css('display', '');

                        JQuery('#bfInitScriptSelection').empty();
                        for (var i = 0; i < appScope.elementScripts.init.length; i++) {
                            var option = document.createElement('option');
                            JQuery(option).val(appScope.elementScripts.init[i].id);
                            JQuery(option).text(appScope.elementScripts.init[i].package + '::' + appScope.elementScripts.init[i].name);
                            if (appScope.elementScripts.init[i].id == mdata.initId) {
                                JQuery(option).get(0).setAttribute('selected', true);
                            }
                            JQuery('#bfInitScriptSelection').append(option);
                        }

                        if (mdata.initFormEntry == 1) {
                            JQuery('#bfInitFormEntry').get(0).checked = true;
                        } else {
                            JQuery('#bfInitFormEntry').get(0).checked = false;
                        }

                        if (mdata.initPageEntry == 1) {
                            JQuery('#bfInitPageEntry').get(0).checked = true;
                        } else {
                            JQuery('#bfInitPageEntry').get(0).checked = false;
                        }

                        Joomla.editors.instances["bfInitCode"].setValue(mdata.initCode);

                        switch (mdata.initCondition) {
                            case 1:
                                JQuery('.bfInitType').attr('checked', '');
                                JQuery('#bfInitTypeLibrary').attr('checked', true);
                                JQuery('#bfInitScriptLibrary').css('display', '');
                                JQuery('#bfInitScriptCustom').css('display', 'none');
                                JQuery('#bfInitScriptFlags').css('display', '');
                                JQuery('#bfInitScriptLibrary').css('display', '');
                                JQuery('#bfInitScriptCustom').css('display', 'none');
                                appScope.setInitScriptDescription();
                                break;
                            case 2:
                                JQuery('.bfInitType').attr('checked', '');
                                JQuery('#bfInitTypeCustom').attr('checked', true);
                                JQuery('#bfInitScriptFlags').css('display', '');
                                JQuery('#bfInitScriptLibrary').css('display', 'none');
                                JQuery('#bfInitScriptCustom').css('display', '');
                                break;
                            default:
                                JQuery('.bfInitType').attr('checked', '');
                                JQuery('#bfInitTypeNone').attr('checked', true);
                                JQuery('#bfInitScriptFlags').css('display', 'none');
                                JQuery('#bfInitScriptLibrary').css('display', 'none');
                                JQuery('#bfInitScriptCustom').css('display', 'none');
                        }
                    }
                };

                this.populateElementActionScript = function () {

                    var mdata = appScope.getProperties(appScope.selectedTreeElement);
                    if (mdata) {

                        JQuery('#bfActionScript').css('display', '');

                        if (mdata.bfType == 'bfStripe' || mdata.bfType == 'bfSofortueberweisung' || mdata.bfType == 'bfPayPal' || mdata.bfType == 'bfIcon' || mdata.bfType == 'bfImageButton' || mdata.bfType == 'bfSubmitButton') {
                            JQuery('.bfAction').css('display', 'none');
                            JQuery('.bfActionLabel').css('display', 'none');
                            JQuery('#bfActionClick').css('display', '');
                            JQuery('#bfActionClickLabel').css('display', '');
                        } else {
                            JQuery('.bfAction').css('display', '');
                            JQuery('.bfActionLabel').css('display', '');
                        }

                        JQuery('#bfActionsScriptSelection').empty();

                        for (var i = 0; i < appScope.elementScripts.action.length; i++) {

                            var option = document.createElement('option');

                            JQuery(option).val(appScope.elementScripts.action[i].id);
                            JQuery(option).text(appScope.elementScripts.action[i].package + '::' + appScope.elementScripts.action[i].name);

                            if (appScope.elementScripts.action[i].id == mdata.actionId) {

                                JQuery(option).get(0).setAttribute('selected', true);
                            }

                            JQuery('#bfActionsScriptSelection').append(option);
                        }

                        if (mdata.actionClick == 1) {
                            JQuery('#bfActionClick').get(0).checked = true;
                        } else {
                            JQuery('#bfActionClick').get(0).checked = false;
                        }

                        if (mdata.actionBlur == 1) {
                            JQuery('#bfActionBlur').get(0).checked = true;
                        } else {
                            JQuery('#bfActionBlur').get(0).checked = false;
                        }

                        if (mdata.actionChange == 1) {
                            JQuery('#bfActionChange').get(0).checked = true;
                        } else {
                            JQuery('#bfActionChange').get(0).checked = false;
                        }

                        if (mdata.actionFocus == 1) {
                            JQuery('#bfActionFocus').get(0).checked = true;
                        } else {
                            JQuery('#bfActionFocus').get(0).checked = false;
                        }

                        if (mdata.actionSelect == 1) {
                            JQuery('#bfActionSelect').get(0).checked = true;
                        } else {
                            JQuery('#bfActionSelect').get(0).checked = false;
                        }

                        Joomla.editors.instances["bfActionCode"].setValue(mdata.actionCode);

                        switch (mdata.actionCondition) {
                            case 1:
                                JQuery('.bfActionType').attr('checked', '');
                                JQuery('#bfActionTypeLibrary').attr('checked', true);
                                JQuery('#bfActionScriptLibrary').css('display', '');
                                JQuery('#bfActionScriptCustom').css('display', 'none');
                                JQuery('#bfActionScriptFlags').css('display', '');
                                JQuery('#bfActionScriptLibrary').css('display', '');
                                JQuery('#bfActionScriptCustom').css('display', 'none');
                                appScope.setActionScriptDescription();
                                break;
                            case 2:
                                JQuery('.bfActionType').attr('checked', '');
                                JQuery('#bfActionTypeCustom').attr('checked', true);
                                JQuery('#bfActionScriptFlags').css('display', '');
                                JQuery('#bfActionScriptLibrary').css('display', 'none');
                                JQuery('#bfActionScriptCustom').css('display', '');
                                break;
                            default:
                                JQuery('.bfActionType').attr('checked', '');
                                JQuery('#bfActionTypeNone').attr('checked', true);
                                JQuery('#bfActionScriptFlags').css('display', 'none');
                                JQuery('#bfActionScriptLibrary').css('display', 'none');
                                JQuery('#bfActionScriptCustom').css('display', 'none');
                        }

                    }
                };

                this.createTreeItem = function (obj) {
                    if (appScope.selectedTreeElement) {
                        switch (appScope.getNodeClass(appScope.selectedTreeElement)) {
                            case 'bfQuickModePageClass':
                            case 'bfQuickModeSectionClass':
                                if (obj.attributes['class'] != 'bfQuickModePageClass') {
                                    var item = appScope.findDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), appScope.dataObject);
                                    if (item) {
                                        if (item.children) {
                                            item.children[item.children.length] = obj;
                                        } else {
                                            alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_NO_CHILDREN_ERROR')); ?>");
                                        }
                                    }
                                } else {
                                    alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_NEW_SECTION_ERROR')); ?>");
                                }
                                break;
                            case 'bfQuickModeRootClass':
                                if (obj.attributes['class'] == 'bfQuickModePageClass' && appScope.dataObject && appScope.dataObject.children) {
                                    appScope.dataObject.children[appScope.dataObject.children.length] = obj;
                                } else {
                                    alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_NEW_SECTION_ERROR')); ?>");
                                }
                                break;
                            default:
                                alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_NEW_SECTION_ERROR')); ?>");
                        }
                        JQuery.tree_reference('bfElementExplorer').refresh();
                    }
                };

                /**
                 Section properties
                 */
                this.saveSectionProperties = function () {
                    var mdata = appScope.getProperties(appScope.selectedTreeElement);
                    if (mdata) {
                        var item = appScope.findDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), appScope.dataObject);
                        if (item) {
                            mdata.bfType = JQuery('#bfSectionType').val();
                            mdata.displayType = JQuery('#bfSectionDisplayType').val();
                            mdata.title = JQuery('#bfSectionTitle').val();
                            mdata['title_translation<?php echo $active_language_code; ?>'] = JQuery('#bfSectionTitleTrans').val();

                            mdata.name = JQuery('#bfSectionName').val();
                            mdata.off = JQuery('#bfSectionAdvancedTurnOff').attr('checked');

                            item.properties = mdata;
                            item.data.title = JQuery('#bfSectionTitle').val();
                        }
                    }
                };

                this.populateSectionProperties = function () {
                    if (appScope.selectedTreeElement) {
                        var mdata = appScope.getProperties(appScope.selectedTreeElement);
                        // compat 723
                        if (typeof mdata.off == "undefined") {
                            mdata['off'] = false;
                        }
                        // compat 723 end
                        if (mdata) {
                            var item = appScope.findDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), appScope.dataObject);
                            if (item) {
                                item.data.title = mdata.title;

                                JQuery('#bfSectionType').val(mdata.bfType);
                                JQuery('#bfSectionDisplayType').val(mdata.displayType);

                                JQuery('#bfSectionTitle').val(mdata.title);
                                JQuery('#bfSectionTitleTrans').val(typeof mdata['title_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['title_translation<?php echo $active_language_code; ?>'] : "");

                                // compat 723
                                JQuery('#bfSectionName').val(typeof mdata.name == "undefined" ? '' : mdata.name);
                                // compat 723 end
                                JQuery('#bfSectionAdvancedTurnOff').attr('checked', mdata.off);
                            }
                        }
                    }
                };

                /**
                 Form properties
                 */
                this.saveFormProperties = function () {
                    var mdata = appScope.getProperties(appScope.selectedTreeElement);
                    if (mdata) {
                        var item = appScope.findDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), appScope.dataObject);
                        if (item) {
                            mdata.title = JQuery('#bfFormTitle').val();
                            mdata['title_translation<?php echo $active_language_code; ?>'] = JQuery('#bfFormTitleTrans').val();

                            mdata.name = JQuery('#bfFormName').val();
                            mdata.description = JQuery('#bfFormDescription').val();
                            mdata.mailRecipient = JQuery('#bfFormMailRecipient').val();
                            mdata.mailNotification = JQuery('#bfFormMailNotification').attr('checked');
                            mdata.submitInclude = JQuery('#bfSubmitIncludeYes').attr('checked');
                            mdata.themebootstrapLabelTop = JQuery('#bfThemeBootstrapLabelTopYes').attr('checked');
                            mdata.themeusebootstraplegacy = typeof JQuery('#bfThemeBootstrapUseLegacyYes').get(0) != "undefined" ? JQuery('#bfThemeBootstrapUseLegacyYes').attr('checked') : false;
                            mdata.themebootstrapUseHeroUnit = JQuery('#bfThemeBootstrapUseHeroUnitYes').attr('checked');
                            mdata.themebootstrapUseWell = JQuery('#bfThemeBootstrapUseWellYes').attr('checked');
                            mdata.themebootstrapUseProgress = JQuery('#bfThemeBootstrapUseProgressYes').attr('checked');

                            mdata.themebootstrapThemeEngine = JQuery('#bfThemeBootstrapThemeBootstrap').attr('checked') ? 'bootstrap' : 'breezingforms';

                            mdata.themebootstrapMode = JQuery('#bfThemeBootstrapModeYes').attr('checked');
                            mdata.themebootstrapUse3 = JQuery('#bfThemeBootstrapUse3Yes').attr('checked');
                            mdata.themebootstrap3builtin = JQuery('#bfThemeBootstrap3BuiltInYes').attr('checked');
                            mdata.themebootstrap3classpfx = JQuery('#bfThemeBootstrap3Classpfx').val();

                            mdata.submitLabel = JQuery('#bfFormSubmitLabel').val();
                            mdata['submitLabel_translation<?php echo $active_language_code; ?>'] = JQuery('#bfFormSubmitLabelTrans').val();

                            mdata.cancelInclude = JQuery('#bfCancelIncludeYes').attr('checked');

                            mdata.cancelLabel = JQuery('#bfFormCancelLabel').val();
                            mdata['cancelLabel_translation<?php echo $active_language_code; ?>'] = JQuery('#bfFormCancelLabelTrans').val();

                            mdata.pagingInclude = JQuery('#bfPagingIncludeYes').attr('checked');

                            mdata.pagingNextLabel = JQuery('#bfFormPagingNextLabel').val();
                            mdata['pagingNextLabel_translation<?php echo $active_language_code; ?>'] = JQuery('#bfFormPagingNextLabelTrans').val();

                            mdata.pagingPrevLabel = JQuery('#bfFormPagingPrevLabel').val();
                            mdata['pagingPrevLabel_translation<?php echo $active_language_code; ?>'] = JQuery('#bfFormPagingPrevLabelTrans').val();

                            mdata.theme = JQuery('#bfTheme').val();
                            mdata.themebootstrap = JQuery('#bfThemeBootstrap').val();
                            mdata.themebootstrapvars = typeof JQuery('#bfThemeBootstrapVars').get(0) != "undefined" ? JQuery('#bfThemeBootstrapVars').val() : '';
                            if (!mdata.themebootstrapbefore) {
                                mdata['themebootstrapbefore'] = '';
                            }
                            mdata.themebootstrapbefore = typeof JQuery('#bfThemeBootstrapBefore').get(0) != "undefined" ? JQuery('#bfThemeBootstrapBefore').val() : '';
                            mdata.fadeIn = JQuery('#bfElementAdvancedFadeIn').attr('checked');
                            mdata.useErrorAlerts = JQuery('#bfElementAdvancedUseErrorAlerts').attr('checked');

                            mdata.disableJQuery = JQuery('#bfElementAdvancedDisableJQuery').attr('checked');
                            mdata.joomlaHint = JQuery('#bfElementAdvancedJoomlaHint').attr('checked');

                            mdata.mobileEnabled = JQuery('#bfElementAdvancedMobileEnabled').attr('checked');
                            mdata.forceMobile = JQuery('#bfElementAdvancedForceMobile').attr('checked');
                            mdata.forceMobileUrl = JQuery('#bfElementAdvancedForceMobileUrl').val();

                            mdata.useDefaultErrors = JQuery('#bfElementAdvancedUseDefaultErrors').attr('checked');
                            mdata.useBalloonErrors = JQuery('#bfElementAdvancedUseBalloonErrors').attr('checked');
                            mdata.lastPageThankYou = JQuery('#bfFormLastPageThankYou').attr('checked');
                            mdata.rollover = JQuery('#bfElementAdvancedRollover').attr('checked');
                            mdata.rolloverColor = JQuery('#bfElementAdvancedRolloverColor').val();
                            mdata.toggleFields = JQuery('#bfElementAdvancedToggleFields').val();
                            var pagesSize = JQuery('#bfQuickModeRoot').children("ul").children("li").size();
                            if (mdata.lastPageThankYou && pagesSize > 1) {
                                mdata.submittedScriptCondidtion = 2;
                                mdata.submittedScriptCode = 'function ff_' + mdata.name + '_submitted(status, message){if(status==0){ff_switchpage(' + pagesSize + ');}else{alert(message);}}';
                            } else {
                                mdata.submittedScriptCondidtion = -1;
                            }
                            item.properties = mdata;
                        }
                    }
                };

                this.populateFormProperties = function () {
                    if (appScope.selectedTreeElement) {
                        var mdata = appScope.getProperties(appScope.selectedTreeElement);
                        if (mdata) {
                            // setting the node's data
                            var item = appScope.findDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), appScope.dataObject);
                            if (item) {
                                item.data.title = mdata.title;
                                JQuery('#bfFormTitleTrans').val(typeof mdata['title_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['title_translation<?php echo $active_language_code; ?>'] : "");

                                JQuery('#bfElementAdvancedFadeIn').attr('checked', mdata.fadeIn);
                                JQuery('#bfFormLastPageThankYou').attr('checked', mdata.lastPageThankYou);
                                JQuery('#bfElementAdvancedUseErrorAlerts').attr('checked', mdata.useErrorAlerts);

                                JQuery('#bfElementAdvancedDisableJQuery').attr('checked', mdata.disableJQuery);
                                JQuery('#bfElementAdvancedJoomlaHint').attr('checked', mdata.joomlaHint);

                                JQuery('#bfElementAdvancedMobileEnabled').attr('checked', mdata.mobileEnabled);
                                JQuery('#bfElementAdvancedForceMobile').attr('checked', mdata.forceMobile);
                                JQuery('#bfElementAdvancedForceMobileUrl').val(mdata.forceMobileUrl);

                                JQuery('#bfElementAdvancedUseDefaultErrors').attr('checked', mdata.useDefaultErrors);
                                JQuery('#bfElementAdvancedUseBalloonErrors').attr('checked', mdata.useBalloonErrors);
                                if (mdata.submitInclude) {
                                    JQuery('#bfSubmitIncludeYes').attr('checked', true);
                                    JQuery('#bfSubmitIncludeNo').attr('checked', false);
                                } else {
                                    JQuery('#bfSubmitIncludeYes').attr('checked', false);
                                    JQuery('#bfSubmitIncludeNo').attr('checked', true);
                                }
                                if (mdata.themebootstrapLabelTop) {
                                    JQuery('#bfThemeBootstrapLabelTopYes').attr('checked', true);
                                    JQuery('#bfThemeBootstrapLabelTopNo').attr('checked', false);
                                } else {
                                    JQuery('#bfThemeBootstrapLabelTopYes').attr('checked', false);
                                    JQuery('#bfThemeBootstrapLabelTopNo').attr('checked', true);
                                }
                                if (typeof JQuery('#bfThemeBootstrapUseLegacyYes').get(0) != "undefined" && mdata.themeusebootstraplegacy) {
                                    JQuery('#bfThemeBootstrapUseLegacyYes').attr('checked', true);
                                    JQuery('#bfThemeBootstrapUseLegacyNo').attr('checked', false);
                                } else if (typeof JQuery('#bfThemeBootstrapUseLegacyYes').get(0) != "undefined") {
                                    JQuery('#bfThemeBootstrapUseLegacyYes').attr('checked', false);
                                    JQuery('#bfThemeBootstrapUseLegacyNo').attr('checked', true);
                                }

                                if (mdata.themebootstrapMode) {
                                    JQuery('#bfThemeBootstrapModeYes').attr('checked', true);
                                    JQuery('#bfThemeBootstrapModeNo').attr('checked', false);
                                } else {
                                    JQuery('#bfThemeBootstrapModeYes').attr('checked', false);
                                    JQuery('#bfThemeBootstrapModeNo').attr('checked', true);
                                }

                                if (mdata.themebootstrapUse3) {
                                    JQuery('#bfThemeBootstrapUse3Yes').attr('checked', true);
                                    JQuery('#bfThemeBootstrapUse3No').attr('checked', false);
                                } else {
                                    JQuery('#bfThemeBootstrapUse3Yes').attr('checked', false);
                                    JQuery('#bfThemeBootstrapUse3No').attr('checked', true);
                                }

                                if (mdata.themebootstrap3builtin) {
                                    JQuery('#bfThemeBootstrap3BuiltInYes').attr('checked', true);
                                    JQuery('#bfThemeBootstrap3BuiltInNo').attr('checked', false);
                                } else {
                                    JQuery('#bfThemeBootstrap3BuiltInYes').attr('checked', false);
                                    JQuery('#bfThemeBootstrap3BuiltInNo').attr('checked', true);
                                }

                                if (mdata.themebootstrap3classpfx) {

                                    JQuery('#bfThemeBootstrap3Classpfx').val(mdata.themebootstrap3classpfx);
                                }

                                if (mdata.themebootstrapThemeEngine == 'bootstrap') {
                                    JQuery('#bfThemeBootstrapThemeBootstrap').attr('checked', true);
                                    JQuery('#bfThemeBootstrapThemeBreezingForms').attr('checked', false);
                                    JQuery('#bfThemeBootstrapDiv').css("display", "block");
                                    JQuery('#bfThemeBreezingFormsDiv').css("display", "none");

                                    // disable rollover
                                    JQuery("#bfRollOverToggle").css("display", "none");
                                    // disable label positions
                                    JQuery("#bfLabelPositionToggle").css("display", "none");
                                    // disable fading
                                    JQuery("#bfFadingEffectToggle").css("display", "none");

                                } else {
                                    JQuery('#bfThemeBootstrapThemeBootstrap').attr('checked', false);
                                    JQuery('#bfThemeBootstrapThemeBreezingForms').attr('checked', true);
                                    JQuery('#bfThemeBootstrapDiv').css("display", "none");
                                    JQuery('#bfThemeBreezingFormsDiv').css("display", "block");
                                }
                                if (mdata.themebootstrapUseHeroUnit) {
                                    JQuery('#bfThemeBootstrapUseHeroUnitYes').attr('checked', true);
                                    JQuery('#bfThemeBootstrapUseHeroUnitNo').attr('checked', false);
                                } else {
                                    JQuery('#bfThemeBootstrapUseHeroUnitYes').attr('checked', false);
                                    JQuery('#bfThemeBootstrapUseHeroUnitNo').attr('checked', true);
                                }
                                if (mdata.themebootstrapUseWell) {
                                    JQuery('#bfThemeBootstrapUseWellYes').attr('checked', true);
                                    JQuery('#bfThemeBootstrapUseWellNo').attr('checked', false);
                                } else {
                                    JQuery('#bfThemeBootstrapUseWellYes').attr('checked', false);
                                    JQuery('#bfThemeBootstrapUseWellNo').attr('checked', true);
                                }
                                if (mdata.themebootstrapUseProgress) {
                                    JQuery('#bfThemeBootstrapUseProgressYes').attr('checked', true);
                                    JQuery('#bfThemeBootstrapUseProgressNo').attr('checked', false);
                                } else {
                                    JQuery('#bfThemeBootstrapUseProgressYes').attr('checked', false);
                                    JQuery('#bfThemeBootstrapUseProgressNo').attr('checked', true);
                                }


                                JQuery('#bfFormSubmitLabel').val(mdata.submitLabel);
                                JQuery('#bfFormSubmitLabelTrans').val(typeof mdata['submitLabel_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['submitLabel_translation<?php echo $active_language_code; ?>'] : "");

                                if (mdata.cancelInclude) {
                                    JQuery('#bfCancelIncludeYes').attr('checked', true);
                                    JQuery('#bfCancelIncludeNo').attr('checked', false);
                                } else {
                                    JQuery('#bfCancelIncludeYes').attr('checked', false);
                                    JQuery('#bfCancelIncludeNo').attr('checked', true);
                                }

                                JQuery('#bfFormCancelLabel').val(mdata.cancelLabel);
                                JQuery('#bfFormCancelLabelTrans').val(typeof mdata['cancelLabel_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['cancelLabel_translation<?php echo $active_language_code; ?>'] : "");

                                if (mdata.pagingInclude) {
                                    JQuery('#bfPagingIncludeYes').attr('checked', true);
                                    JQuery('#bfPagingIncludeNo').attr('checked', false);
                                } else {
                                    JQuery('#bfPagingIncludeYes').attr('checked', false);
                                    JQuery('#bfPagingIncludeNo').attr('checked', true);
                                }

                                JQuery('#bfFormPagingNextLabel').val(mdata.pagingNextLabel);
                                JQuery('#bfFormPagingNextLabelTrans').val(typeof mdata['pagingNextLabel_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['pagingNextLabel_translation<?php echo $active_language_code; ?>'] : "");

                                JQuery('#bfFormPagingPrevLabel').val(mdata.pagingPrevLabel);
                                JQuery('#bfFormPagingPrevLabelTrans').val(typeof mdata['pagingPrevLabel_translation<?php echo $active_language_code; ?>'] != "undefined" ? mdata['pagingPrevLabel_translation<?php echo $active_language_code; ?>'] : "");

                                JQuery('#bfTheme').val(mdata.theme);
                                JQuery('#bfThemeBootstrap').val(mdata.themebootstrap);
                                JQuery('#bfThemeBootstrapBefore').val(mdata.themebootstrap);
                                JQuery('#bfElementAdvancedRollover').attr('checked', mdata.rollover);
                                JQuery('#bfElementAdvancedRolloverColor').val(mdata.rolloverColor);
                                JQuery('#bfElementAdvancedToggleFields').val(mdata.toggleFields);
                            }
                        }
                    }
                };

                /**
                 Page Properties
                 */
                this.savePageProperties = function () {
                    var mdata = appScope.getProperties(appScope.selectedTreeElement);
                    if (mdata) {
                        var item = appScope.findDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), appScope.dataObject);
                        if (item) {
                            item.properties = mdata;
                        }
                    }
                };

                this.populatePageProperties = function () {
                    if (appScope.selectedTreeElement) {
                        var mdata = appScope.getProperties(appScope.selectedTreeElement);
                        if (mdata) {
                            // setting the node's data
                            var item = appScope.findDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), appScope.dataObject);
                            if (item) {
                                // no properties yet to set
                            }
                        }
                    }
                };

                /**
                 Main application
                 */
                this.toggleProperties = function (property) {
                    JQuery('.bfProperties').css('display', 'none');
                    JQuery('#' + property).css('display', '');
                };

                this.toggleAdvanced = function (property) {
                    JQuery('.bfAdvanced').css('display', 'none');
                    JQuery('#' + property).css('display', '');
                };

                JQuery('#bfElementExplorer').tree(
                    {
                        ui: {
                            theme_name: "apple",
                            context: [
                                {
                                    id: 'copy',
                                    label: 'Copy',
                                    visible: function (NODE, TREE_OBJ) {
                                        var source = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                        if (source.attributes['class'] == 'bfQuickModeSectionClass' || source.attributes['class'] == 'bfQuickModeElementClass') {
                                            return true;
                                        }
                                        return false;
                                    },
                                    action: function (NODE, TREE_OBJ) {
                                        var source = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                        if (source.attributes['class'] == 'bfQuickModeSectionClass' || source.attributes['class'] == 'bfQuickModeElementClass') {
                                            if (source && source.attributes && source.attributes.id) {
                                                appScope.copyTreeElement = source;
                                            }
                                        }
                                    }
                                },
                                {
                                    id: 'paste',
                                    label: 'Paste',
                                    visible: function (NODE, TREE_OBJ) {
                                        if (appScope.copyTreeElement) {
                                            var target = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                            if (target.attributes['class'] == 'bfQuickModeSectionClass' || target.attributes['class'] == 'bfQuickModePageClass') {
                                                return true;
                                            }
                                            return false;
                                        }
                                        return false;
                                    },
                                    action: function (NODE, TREE_OBJ) {
                                        if (appScope.copyTreeElement) {
                                            var target = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                            if (target.attributes['class'] == 'bfQuickModeSectionClass' || target.attributes['class'] == 'bfQuickModePageClass') {
                                                appScope.insertElementInto(clone_obj(appScope.copyTreeElement), target);
                                                setTimeout("JQuery.tree_reference('bfElementExplorer').refresh()", 10); // give it time to close the context menu
                                            }
                                        }
                                    }
                                },
                                {
                                    id: "delete",
                                    label: "Delete",
                                    icon: "remove.png",
                                    visible: function (NODE, TREE_OBJ) {
                                        var ok = true;
                                        JQuery.each(NODE, function () {
                                            if (TREE_OBJ.check("deletable", this) == false)
                                                ok = false;
                                            return false;
                                        });
                                        return ok;
                                    },
                                    action: function (NODE, TREE_OBJ) {
                                        JQuery.each(NODE, function () {
                                            TREE_OBJ.remove(this);
                                        });
                                    }
                                }

                            ]

                        },
                        selected: 'bfQuickModeRoot',
                        callback: {
                            onselect: function (node, obj) {
                                appScope.selectedTreeElement = node;
                                JQuery('#bfPropertySaveButton').css('display', '');
                                JQuery('#bfPropertySaveButtonTop').css('display', '');
                                JQuery('#bfAdvancedSaveButton').css('display', '');
                                JQuery('#bfAdvancedSaveButtonTop').css('display', '');
                                switch (appScope.getNodeClass(node)) {
                                    case 'bfQuickModeRootClass':
                                        appScope.toggleProperties('bfFormProperties');
                                        appScope.toggleAdvanced('bfFormAdvanced');
                                        appScope.populateFormProperties();
                                        break;
                                    case 'bfQuickModeSectionClass':
                                        appScope.toggleProperties('bfSectionProperties');
                                        appScope.toggleAdvanced('bfSectionAdvanced');
                                        appScope.populateSectionProperties();
                                        //JQuery('#bfAdvancedSaveButton').css('display','none');
                                        //JQuery('#bfAdvancedSaveButtonTop').css('display','none');
                                        break;
                                    case 'bfQuickModeElementClass':
                                        appScope.toggleProperties('bfElementProperties');
                                        appScope.toggleAdvanced('bfElementAdvanced');
                                        appScope.populateSelectedElementProperties();
                                        break;
                                    case 'bfQuickModePageClass':
                                        appScope.toggleProperties('bfPageProperties');
                                        appScope.toggleAdvanced('bfPageAdvanced');
                                        appScope.populatePageProperties();
                                        JQuery('#bfAdvancedSaveButton').css('display', 'none');
                                        JQuery('#bfAdvancedSaveButtonTop').css('display', 'none');
                                        break;
                                }
                            },
                            onload: function (obj) {

                            },
                            onopen: function (NODE, TREE_OBJ) {
                                var source = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                source.state = 'open';
                            },
                            onclose: function (NODE, TREE_OBJ) {
                                var source = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                source.state = 'close';
                            },
                            ondelete: function (NODE, TREE_OBJ, RB) {
                                appScope.selectedTreeElement = null;
                                appScope.deleteDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                var target = appScope.findDataObjectItem(JQuery('#bfQuickModeRoot').attr('id'), appScope.dataObject);
                                if (target && !target.children) {
                                    target.children = new Array();
                                }
                                // restoring page numbers
                                if (target && target.children) {
                                    if (target.attributes['class'] == 'bfQuickModeRootClass') {
                                        for (var i = 0; i < target.children.length; i++) {
                                            if (target.children[i].attributes['class'] == 'bfQuickModePageClass') {
                                                var mdata = appScope.getProperties(JQuery('#' + target.children[i].attributes.id));
                                                if (mdata) {
                                                    target.children[i].attributes.id = 'bfQuickModePage' + (i + 1);
                                                    target.children[i].data.title = "<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_PAGE')) ?> " + (i + 1);
                                                    target.children[i].properties.pageNumber = i + 1;
                                                }
                                            }
                                        }
                                        // taking care of last page as thank you page
                                        var pagesSize = target.children.length;
                                        if (target.properties.lastPageThankYou && pagesSize > 1) {
                                            target.properties.submittedScriptCondidtion = 2;
                                            target.properties.submittedScriptCode = 'function ff_' + target.properties.name + '_submitted(status, message){if(status==0){ff_switchpage(' + pagesSize + ');}else{alert(message);}}';
                                        } else {
                                            target.properties.submittedScriptCondidtion = -1;
                                        }
                                    }
                                }
                                setTimeout("JQuery.tree_reference('bfElementExplorer').refresh()", 10); // give it time to close the context menu
                            },
                            onmove: function (NODE, REF_NODE, TYPE, TREE_OBJ, RB) {
                                var parent = JQuery.tree_reference('bfElementExplorer').parent(NODE);
                                if (!parent) {
                                    parent = '#bfQuickModeRoot';
                                }
                                children = parent.children("ul").children("li");
                                if (children && children.length && children.length > 0) {
                                    for (var i = 0; i < children.length; i++) {
                                        if (JQuery(NODE).attr('id') == children[i].id) {
                                            appScope.moveDataObjectItem(JQuery(NODE).attr('id'), JQuery(parent).attr('id'), i, appScope.dataObject);
                                            break;
                                        }
                                    }
                                }
                                JQuery.tree_reference('bfElementExplorer').refresh();
                            }
                        },
                        rules: {
                            metadata: 'mdata',
                            use_inline: true,
                            deletable: 'none',
                            creatable: 'none',
                            renameable: 'none',

                            draggable: ['section', 'element', 'page'],
                            dragrules: [
                                'element inside section',
                                'section inside section',
                                'element inside page',
                                'section inside page',
                                'element after element',
                                'element before element',
                                'element after section',
                                'element before section',
                                'section after element',
                                'section before element',
                                'section after section',
                                'section before section',
                                'page before page',
                                'page after page'
                            ]
                        },
                        data: {
                            type: "json",
                            json: [appScope.dataObject]
                        }
                    }
                );

                this.saveButton = function () {
                    var error = false;
                    if (appScope.selectedTreeElement) {

                        switch (appScope.getNodeClass(appScope.selectedTreeElement)) {
                            case 'bfQuickModeRootClass':
                                if (JQuery.trim(JQuery('#bfFormTitle').val()) == '') {
                                    alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_ERROR_ENTER_TITLE')) ?>");
                                    error = true;
                                }
                                if (JQuery.trim(JQuery('#bfFormName').val()) == '') {
                                    alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_ERROR_ENTER_NAME')) ?>");
                                    error = true;
                                }
                                var myRegxp = /^([a-zA-Z0-9_]+)$/;
                                if (!myRegxp.test(JQuery('#bfFormName').val())) {
                                    alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_ERROR_ENTER_NAME_CHARACTERS')) ?>");
                                    error = true;
                                }
                                if (!error) {
                                    appScope.saveFormProperties();
                                }
                                break;
                            case 'bfQuickModeSectionClass':
                                if (JQuery.trim(JQuery('#bfSectionName').val()) == '') {
                                    alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_ERROR_ENTER_NAME')) ?>");
                                    error = true;
                                }
                                if (!error) {
                                    appScope.saveSectionProperties();
                                }
                                break;
                            case 'bfQuickModeElementClass':
                                if (JQuery.trim(JQuery('#bfElementLabel').val()) == '') {
                                    alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_ERROR_ENTER_LABEL')) ?>");
                                    error = true;
                                }
                                if (JQuery.trim(JQuery('#bfElementName').val()) == '') {
                                    alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_ERROR_ENTER_NAME')) ?>");
                                    error = true;
                                }
                                var myRegxp = /^([a-zA-Z0-9_]+)$/;
                                if (!myRegxp.test(JQuery('#bfElementName').val())) {
                                    alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_ERROR_ENTER_NAME_CHARACTERS')) ?>");
                                    error = true;
                                }

                                var items = new Array();
                                appScope.getItemsFlattened(appScope.dataObject, items);
                                for (var i = 0; i < items.length; i++) {
                                    if (JQuery(appScope.selectedTreeElement).attr('id') != items[i].attributes.id && JQuery.trim(items[i].properties.bfName) == JQuery.trim(JQuery('#bfElementName').val())) {
                                        alert("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_ERROR_NAME_EXISTS')); ?>" + " " + JQuery.trim(JQuery('#bfElementName').val()) + " (" + JQuery.trim(JQuery('#bfElementLabel').val()) + ")");
                                        error = true;
                                    }
                                }


                                if (!error) {
                                    appScope.saveSelectedElementProperties();
                                }
                            case 'bfQuickModePageClass':
                                appScope.savePageProperties();
                                break;
                        }
                        if (!error) {
                            // TODO: remove the 2nd refresh if found out why this works only on the 2nd
                            JQuery.tree_reference('bfElementExplorer').refresh();
                            JQuery.tree_reference('bfElementExplorer').refresh();

                            JQuery(".bfFadingMessage").html("<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_SETTINGS_UPDATED')) ?>");
                            JQuery(".bfFadingMessage").fadeIn(1000);
                            setTimeout('JQuery(".bfFadingMessage").fadeOut(1000);', 1500);
                        }
                    }
                    return !error;
                };

                JQuery('#bfPropertySaveButton').click(
                    appScope.saveButton
                );

                JQuery('#bfPropertySaveButtonTop').click(
                    appScope.saveButton
                );

                JQuery('#bfAdvancedSaveButton').click(
                    appScope.saveButton
                );

                JQuery('#bfAdvancedSaveButtonTop').click(
                    appScope.saveButton
                );

                JQuery('#bfNewSectionButton').click(
                    function () {
                        var id = "bfQuickModeSection" + (Math.floor(Math.random() * 100000));
                        var obj = {
                            attributes: {
                                "class": 'bfQuickModeSectionClass',
                                id: id,
                                mdata: JQuery.toJSON({ deletable: true, type: 'section' })
                            },
                            properties: {
                                bfType: 'normal',
                                type: 'section',
                                displayType: 'breaks',
                                title: "untitled section",
                                name: id,
                                description: '',
                                off: false
                            }
                            ,
                            state: "open",
                            data: { title: "untitled section", icon: '<?php echo $iconBase . 'icon_section.png' ?>' },
                            children: []
                        };
                        appScope.createTreeItem(obj);
                        JQuery.tree_reference('bfElementExplorer').select_branch(JQuery('#' + id));
                    }
                );

                JQuery('#bfElementType').change(
                    function () {
                        var obj = null;
                        var id = "bfQuickMode" + (Math.floor(Math.random() * 10000000));
                        var selected = JQuery('#bfElementType').val();
                        switch (selected) {
                            case 'bfElementTypeText':
                                obj = appScope.createTextfield(id);
                                break;
                            case 'bfElementTypeRadioGroup':
                                obj = appScope.createRadioGroup(id);
                                break;
                            case 'bfElementTypeCheckboxGroup':
                                obj = appScope.createCheckboxGroup(id);
                                break;
                            case 'bfElementTypeCheckbox':
                                obj = appScope.createCheckbox(id);
                                break;
                            case 'bfElementTypeSelect':
                                obj = appScope.createSelect(id);
                                break;
                            case 'bfElementTypeTextarea':
                                obj = appScope.createTextarea(id);
                                break;
                            case 'bfElementTypeFile':
                                obj = appScope.createFile(id);
                                break;
                            case 'bfElementTypeSubmitButton':
                                obj = appScope.createSubmitButton(id);
                                break;
                            case 'bfElementTypeNumberInput':
                                obj = appScope.createNumberInput(id);
                                break;
                            case 'bfElementTypeHidden':
                                obj = appScope.createHidden(id);
                                break;
                            case 'bfElementTypeSummarize':
                                obj = appScope.createSummarize(id);
                                break;
                            case 'bfElementTypeCaptcha':
                                obj = appScope.createCaptcha(id);
                                break;
                            case 'bfElementTypeReCaptcha':
                                obj = appScope.createReCaptcha(id);
                                break;
                            case 'bfElementTypeCalendar':
                                obj = appScope.createCalendar(id);
                                break;
                            case 'bfElementTypeCalendarResponsive':
                                obj = appScope.createCalendarResponsive(id);
                                break;
                            case 'bfElementTypeStripe':
                                obj = appScope.createStripe(id);
                                break;
                            case 'bfElementTypeSignature':
                                obj = appScope.createSignature(id);
                                break;
                            case 'bfElementTypePayPal':
                                obj = appScope.createPayPal(id);
                                break;
                            case 'bfElementTypeSofortueberweisung':
                                obj = appScope.createSofortueberweisung(id);
                                break;
                        }
                        if (obj) {
                            appScope.replaceDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), obj, appScope.dataObject);
                            JQuery.tree_reference('bfElementExplorer').refresh();
                            JQuery.tree_reference('bfElementExplorer').select_branch(JQuery('#' + id));
                        }
                    }
                );

                this.setActionScriptDescription = function () {
                    for (var i = 0; i < appScope.elementScripts.action.length; i++) {
                        if (JQuery('#bfActionsScriptSelection').val() == appScope.elementScripts.action[i].id) {
                            JQuery('#bfActionsScriptSelectionDescription').text(appScope.elementScripts.action[i].description);
                        }
                    }
                };

                JQuery('#bfActionsScriptSelection').change(
                    function () {
                        appScope.setActionScriptDescription();
                    }
                );

                this.setInitScriptDescription = function () {
                    for (var i = 0; i < appScope.elementScripts.init.length; i++) {
                        if (JQuery('#bfInitScriptSelection').val() == appScope.elementScripts.init[i].id) {
                            JQuery('#bfInitSelectionDescription').text(appScope.elementScripts.init[i].description);
                        }
                    }
                };

                JQuery('#bfInitScriptSelection').change(
                    function () {
                        appScope.setInitScriptDescription();
                    }
                );

                this.setValidationScriptDescription = function () {
                    for (var i = 0; i < appScope.elementScripts.validation.length; i++) {
                        if (JQuery('#bfValidationScriptSelection').val() == appScope.elementScripts.validation[i].id) {
                            JQuery('#bfValidationScriptSelectionDescription').text(appScope.elementScripts.validation[i].description);
                        }
                    }
                };

                JQuery('#bfValidationScriptSelection').change(
                    function () {
                        appScope.setValidationScriptDescription();
                    }
                );

                JQuery('#bfNewElementButton').click(
                    function () {
                        var id = "bfQuickMode" + (Math.floor(Math.random() * 10000000));
                        var obj = appScope.createTextfield(id);
                        appScope.createTreeItem(obj);
                        JQuery.tree_reference('bfElementExplorer').select_branch(JQuery('#' + id));
                    }
                );

                JQuery('#bfNewPageButton').click(
                    function () {
                        var pageNumber = JQuery('#bfQuickModeRoot').children("ul").children("li").size() == 0 ? 1 : JQuery('#bfQuickModeRoot').children("ul").children("li").size() + 1;
                        var id = "bfQuickModePage" + pageNumber;

                        // taking care of thank you page if a new page is added
                        var item = appScope.findDataObjectItem('bfQuickModeRoot', appScope.dataObject);
                        var pagesSize = JQuery('#bfQuickModeRoot').children("ul").children("li").size();
                        if (item.properties.lastPageThankYou && pagesSize > 0) {
                            item.properties.submittedScriptCondidtion = 2;
                            item.properties.submittedScriptCode = 'function ff_' + item.properties.name + '_submitted(status, message){if(status==0){ff_switchpage(' + (pagesSize + 1) + ');}else{alert(message);}}';
                        } else {
                            item.properties.submittedScriptCondidtion = -1;
                        }

                        var obj = {
                            attributes: {
                                "class": 'bfQuickModePageClass',
                                id: id,
                                mdata: JQuery.toJSON({ deletable: true, type: 'page' })
                            },
                            properties: { type: 'page', pageNumber: pageNumber, pageIntro: '' },
                            state: "open",
                            data: {
                                title: "<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_PAGE')) ?> " + pageNumber,
                                icon: '<?php echo $iconBase . 'icon_page.png' ?>'
                            },
                            children: []
                        };
                        appScope.createTreeItem(obj);
                        JQuery.tree_reference('bfElementExplorer').select_branch(JQuery('#' + id));
                    }
                );

                JQuery('#menutab').tabs({
                    select: function (e, ui) {
                    }
                });
            }

            window.onload = function () {

                // works around a bug in Firefox 40.0 that prevents you from selecting anything in the editor
                if (JQuery.browser.mozilla) {
                    JQuery("option").live('click', function () {
                        var options = JQuery(this).closest("select").get(0).options;
                        for (var i = 0; i < options.length; i++) {
                            if (options[i] == JQuery(this).get(0)) {
                                JQuery(this).closest("select").get(0).selectedIndex = i;
                                JQuery(this).closest("select").trigger('change');
                                JQuery(this).closest("select").blur();
                                break;
                            }
                        }
                    });
                }

                app = new BF_QuickModeApp();
                var mdata = app.getProperties(app.selectedTreeElement);
                if (mdata) {
                    var item = app.findDataObjectItem('bfQuickModeRoot', app.dataObject);
                    if (item) {
                        mdata.title = "<?php echo addslashes($formTitle) ?>";
                        mdata.name = "<?php echo addslashes($formName) ?>";
                        mdata.description = "<?php echo addslashes(str_replace("\n", '', str_replace("\r", '', $formDesc))) ?>";
                        mdata.mailRecipient = "<?php echo addslashes($formEmailadr) ?>";
                        mdata.mailNotification = "<?php echo addslashes($formEmailntf) == 2 || addslashes($formEmailntf) == 1 ? true : false ?>";
                        item.properties = mdata;
                    }
                }
            };

            function createInitCode() {
                var mdata = app.getProperties(app.selectedTreeElement);
                if (mdata) {
                    form = document.bfForm;
                    name = mdata.bfName;
                    if (name == '') {
                        alert('Please enter the element name first.');
                        return;
                    } // if
                    if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREAINIT'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>"))
                        return;
                    code =
                        "function ff_" + name + "_init(element, condition)\n" +
                        "{\n" +
                        "    switch (condition) {\n";
                    if (form.bfInitFormEntry.checked)
                        code +=
                            "        case 'formentry':\n" +
                            "            break;\n";
                    if (form.bfInitPageEntry.checked)
                        code +=
                            "        case 'pageentry':\n" +
                            "            break;\n";
                    code +=
                        "        default:;\n" +
                        "    } // switch\n" +
                        "} // ff_" + name + "_init\n";
                    oldcode = Joomla.editors.instances["bfInitCode"].getValue();
                    if (oldcode != '')
                        Joomla.editors.instances["bfInitCode"].setValue(
                            code +
                            "\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
                            oldcode);
                    else
                        Joomla.editors.instances["bfInitCode"].setValue(code);
                }
            } // createInitCode

            function createValidationCode() {
                var mdata = app.getProperties(app.selectedTreeElement);
                if (mdata) {
                    form = document.bfForm;
                    name = mdata.bfName;
                    if (name == '') {
                        alert('Please enter the element name first.');
                        return;
                    } // if
                    if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREAVALID'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>"))
                        return;
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
                    oldcode = Joomla.editors.instances["bfValidationCode"].getValue();
                    if (oldcode != '')
                        Joomla.editors.instances["bfValidationCode"].setValue(
                            code +
                            "\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
                            oldcode);
                    else
                        Joomla.editors.instances["bfValidationCode"].setValue(code);
                }
            } // createValidationCode

            function createActionCode(element) {
                var mdata = app.getProperties(app.selectedTreeElement);
                if (mdata) {
                    form = document.bfForm;
                    name = mdata.bfName;
                    if (name == '') {
                        alert('Please enter the element name first.');
                        return;
                    } // if
                    if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CREAACTION'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_EXISTAPP'); ?>"))
                        return;
                    code =
                        "function ff_" + name + "_action(element, action)\n" +
                        "{\n" +
                        "    switch (action) {\n";
                    if (form.bfActionClick)
                        if (form.bfActionClick.checked)
                            code +=
                                "        case 'click':\n" +
                                "            break;\n";
                    if (form.bfActionBlur)
                        if (form.bfActionBlur.checked)
                            code +=
                                "        case 'blur':\n" +
                                "            break;\n";
                    if (form.bfActionChange)
                        if (form.bfActionChange.checked)
                            code +=
                                "        case 'change':\n" +
                                "            break;\n";
                    if (form.bfActionFocus)
                        if (form.bfActionFocus.checked)
                            code +=
                                "        case 'focus':\n" +
                                "            break;\n";
                    if (form.bfActionSelect)
                        if (form.bfActionSelect.checked)
                            code +=
                                "        case 'select':\n" +
                                "            break;\n";
                    code +=
                        "        default:;\n" +
                        "    } // switch\n" +
                        "} // ff_" + name + "_action\n";

                    oldcode = Joomla.editors.instances["bfActionCode"].getValue();
                    if (oldcode != '')
                        Joomla.editors.instances["bfActionCode"].setValue(
                            code +
                            "\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_OLDBELOW'); ?> --------------\n\n" +
                            oldcode);
                    else
                        Joomla.editors.instances["bfActionCode"].setValue(code);
                }
            } // createActionCode

            function postTheStuff() {
                JQuery.ajax({
                    type: 'POST',
                    url: 'index.php',
                    data: {
                        option: 'com_breezingforms',
                        act: "quickmode",
                        task: "doAjaxSave",
                        form: document.adminForm.form.value,
                        chunksLength: chunks.length,
                        chunkIdx: chunki,
                        chunk: chunks[chunki],
                        rndAdd: rndAdd
                    },
                    success: function (data) {

                        if (data != '' && data != 0 && !isNaN(data)) {

                            document.adminForm.form.value = data;
                            document.adminForm.submit();

                        } else if (JQuery.trim(data) == '') {
                            JQuery("#bfSaveQueue").get(0).innerHTML = "<?php echo addslashes(BFText::_('COM_BREEZINGFORMS_LOAD_PACKAGE')); ?> " + (chunki + 1) + " <?php echo addslashes(BFText::_('COM_BREEZINGFORMS_LOAD_PACKAGE_OF')); ?> " + (chunks.length - 1);
                            chunki++;
                            setTimeout(postTheStuff, 100);

                        }
                    },
                    error: function () {
                        JQuery("#bfSaveQueue").get(0).innerHTML = 'connection problem, trying again in 120 seconds, please wait...';
                        var secs = 120;
                        var clear = null;
                        clear = setInterval(
                            function () {
                                JQuery("#bfSaveQueue").get(0).innerHTML = 'connection problem, trying again in ' + secs + ' seconds, please wait...';
                                secs--;
                                if (secs <= 0) {
                                    clearInterval(clear);
                                    setTimeout(postTheStuff, 100);
                                }
                            }
                            , 1000);

                    },
                    async: false
                });
            }

            var chunki = 0;
            var rndAdd = Math.random();
            var chunks = new Array();
            var saveButtonClicked = false;

            JQuery(document).ready(function () {

                JQuery('#adminForm').get(0).onsubmit = function () {
                    return false;
                };

                JQuery('joomla-toolbar-button').click(function (e) {

                    e.preventDefault();

                    let pressbutton = JQuery(this).attr('task');

                    var form = document.adminForm;

                    switch (pressbutton) {

                        case 'close':
                            JQuery('#adminForm').get(0).onsubmit = function () {
                                return false;
                            };
                            location.href = "index.php?option=com_breezingforms&act=manageforms";
                            break;
                        case 'save':

                            JQuery('#adminForm').get(0).onsubmit = function () {
                                return false;
                            };

                            if (!app.saveButton()) {
                                saveButtonClicked = false;
                                return;
                            }

                            if (saveButtonClicked) {
                                return;
                            }

                            saveButtonClicked = true;

                            form.task.value = 'save';
                            form.act.value = 'quickmode';

                            var base = 'base';
                            var sixty_four = '64Encode';

                            var cVal = JQuery[base + sixty_four](JSON.stringify(app.dataObject));
                            JQuery.ajaxSetup({ async: false });
                            rndAdd = Math.random();
                            chunks = new Array();
                            var chunk = '';
                            if (cVal.length > 10000) {
                                var cnt = 0;
                                for (var i = 0; i < cVal.length; i++) {
                                    chunk += cVal[i];
                                    cnt++;
                                    if (cnt == 20000 || (i + 1 == cVal.length && cnt + 1 < 20000)) {
                                        chunks.push(chunk);
                                        chunk = '';
                                        cnt = 0;
                                    }
                                }
                            } else {
                                chunks.push(cVal);
                            }

                            if (chunks.length > 1) {
                                JQuery("#bfSaveQueue").css("display", "");
                                JQuery("#bfSaveQueue").bfcenter();
                                JQuery("#bfSaveQueue").css("visibility", "visible");
                            }

                            postTheStuff();

                            break;
                        case 'preview':

                            jQuery('#adminForm').get(0).onsubmit = function () {
                                return false;
                            };
                            jQuery('#bfPreviewModal').modal('show');

                            break;
                        case 'preview_site':

                            jQuery('#adminForm').get(0).onsubmit = function () {
                                return false;
                            };
                            jQuery('#bfPreviewModal2').modal('show');

                            break;
                    }

                    return false;
                });

            });

            function addslashes(str) {
                return (str + '').replace(/([\\"'])/g, "\\$1").replace(/\0/g, "\\0");
            }

            function clone_obj(obj) {
                var c = obj instanceof Array ? [] : {};

                for (var i in obj) {
                    var prop = obj[i];

                    if (typeof prop == 'object') {
                        if (prop instanceof Array) {
                            c[i] = [];

                            for (var j = 0; j < prop.length; j++) {
                                if (typeof prop[j] != 'object') {
                                    c[i].push(prop[j]);
                                } else {
                                    c[i].push(clone_obj(prop[j]));
                                }
                            }
                        } else {
                            c[i] = clone_obj(prop);
                        }
                    } else {
                        c[i] = prop;
                    }
                }

                return c;
            }

            jQuery(document).ready(function () {

                let validationCodeVisible = false;
                let initCodeVisible = false;
                let actionCodeVisible = false;

                setInterval(function () {

                    if (!actionCodeVisible && jQuery('#bfActionScriptCustom').is(':visible')) {
                        actionCodeVisible = true;
                        // XDA-GIL - 20240112 - refresh seems to not exit with CodeMirror v6.  
                        //Joomla.editors.instances["bfActionCode"].refresh();
                    } else if (initCodeVisible && jQuery('#bfActionScriptCustom').is(':hidden')) {
                        actionCodeVisible = false;
                    }

                    if (!initCodeVisible && jQuery('#bfInitScriptCustom').is(':visible')) {
                        initCodeVisible = true;
                        // XDA-GIL - 20240112 - refresh seems to not exit with CodeMirror v6.  
                        //Joomla.editors.instances["bfInitCode"].refresh();
                    } else if (initCodeVisible && jQuery('#bfInitScriptCustom').is(':hidden')) {
                        initCodeVisible = false;
                    }

                    if (!validationCodeVisible && jQuery('#bfValidationScriptCustom').is(':visible')) {
                        validationCodeVisible = true;
                        // XDA-GIL - 20240112 - refresh seems to not exit with CodeMirror v6.  
                        //Joomla.editors.instances["bfValidationCode"].refresh();
                    } else if (validationCodeVisible && jQuery('#bfValidationScriptCustom').is(':hidden')) {
                        validationCodeVisible = false;
                    }

                }, 500);

            });

        </script>

        <?php
        echo JHtmlBootstrap::renderModal(
            'bfPreviewModal',
            array(
                'url' => Uri::root() . "index.php?format=html&tmpl=component&option=com_breezingforms&ff_form=" . $formId . "&ff_page=1",
                'width' => '1024px',
                'height' => '500px'
            )
        );

        echo JHtmlBootstrap::renderModal(
            'bfPreviewModal2',
            array(
                'url' => Uri::root() . "index.php?format=html&option=com_breezingforms&ff_form=" . $formId . "&ff_page=1",
                'width' => '1024px',
                'height' => '500px'
            )
        );
        ?>

        <div style="float:left; margin-right: 3px;">
            <?php JToolBarHelper::custom('save', 'save.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_QUICKMODE_SAVE'), false); ?>
            <?php
            if ($formId != 0) {
                JToolBarHelper::custom('preview', 'publish.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_QUICKMODE_PREVIEW'), false);
                JToolBarHelper::custom('preview_site', 'publish.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMS_SITE_PREVIEW'), false);
            }
            ?>
            <?php JToolBarHelper::custom('close', 'cancel.png', 'cancel_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_QUICKMODE_CLOSE'), false); ?>
            <form action="index.php" method="post" name="adminForm" id="adminForm">
                <input type="hidden" name="option" value="com_breezingforms" />
                <input type="hidden" name="act" value="quickmode" />
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="form" value="<?php echo $formId; ?>" />
                <input type="hidden" name="active_language_code" value="<?php echo $active_language_code; ?>" />
                <input type="hidden" name="sizeTplCode" value="0" />
            </form>
        </div>
        <?php
        $menutabcss = 'width: 100%;';
        $menutabcss = 'width: 100%;';
        ?>
        <style type="text/css">
            #bfQuickModeRight #menutab {
                <?php echo $menutabcss; ?>
            }
        </style>
        <?php
        jimport('joomla.application.component.helper');
        $default = JComponentHelper::getParams('com_languages')->get('site');

        Factory::getDocument()->addScriptDeclaration('
        jQuery(document).ready(function () {
            jQuery(\'.bfTrans\').css("display", "none");
        });
        ');
        if ($formId > 0 && count(JLanguageHelper::getLanguages()) > 1) {
            if ($active_language_code != '' && $active_language_code != $default) {
                Factory::getDocument()->addScriptDeclaration('
                jQuery(document).ready(function () {
                    jQuery(\'.bfTrans\').css("display", "block");
                });
                ');
            }
            ?>
            <div onclick="location.href = 'index.php?option=com_breezingforms&format=html&act=quickmode&formName=translationtest&form=<?php echo $formId ?>&active_language_code='"
                class="bfLanguageButton<?php echo $active_language_code == $default || $active_language_code == '' ? ' bfLanguageButtonActive' : '' ?>">
                <?php echo $default; ?>
            </div>
            <?php
            $languages = JLanguageHelper::getLanguages();
            foreach ($languages as $language) {
                if ($language->lang_code != $default) {
                    ?>
                    <div onclick="location.href = 'index.php?option=com_breezingforms&format=html&act=quickmode&formName=translationtest&form=<?php echo $formId ?>&active_language_code=<?php echo $language->lang_code; ?>'"
                        class="bfLanguageButton<?php echo $active_language_code == $language->lang_code ? ' bfLanguageButtonActive' : '' ?>">
                        <?php echo $language->lang_code; ?>
                    </div>
                    <?php
                }
            }
        }
        ?>
        <div style="display:none;visibility:hidden;" id="bfSaveQueue"></div>
        <div id="bfQuickModeWrapper" class="bfClearfix">

            <div id="bfQuickModeLeft" class="bfClearfix">

                <form id="newStuffBar" onsubmit="return false;">
                    <button class="btn btn-warning" id="bfNewPageButton">
                        <?php echo BFText::_('COM_BREEZINGFORMS_NEW_PAGE'); ?>
                    </button>
                    <button class="btn btn-warning" id="bfNewSectionButton">
                        <?php echo BFText::_('COM_BREEZINGFORMS_NEW_SECTION'); ?>
                    </button>
                    <button class="btn btn-warning" id="bfNewElementButton">
                        <?php echo BFText::_('COM_BREEZINGFORMS_NEW_ELEMENT'); ?>
                    </button>
                </form>
                <input id="scrollElementList" type="checkbox"><label for="scrollElementList"
                    class="scrollElementListLabel">Scroll element list</label>
                <div id="bfElementExplorer"></div>

            </div> <!-- ##### bfQuickModeLeft end ##### -->


            <div id="bfQuickModeRight" class="bfClearfix">

                <form name="bfForm" onsubmit="return false">

                    <div id="menutab" class="flora">
                        <input id="triggerScrollable" type="checkbox"><label class="triggerScrollableLabel"
                            for="triggerScrollable">Keep panel docked</label>
                        <ul>
                            <li><a onclick="JQuery('.bfFadingMessage').css('display', 'none')" href="#fragment-1"><span>
                                        <div class="tab-items">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_PROPERTIES') ?>
                                        </div>
                                    </span></a>
                            </li>
                            <li><a onclick="JQuery('.bfFadingMessage').css('display', 'none')" href="#fragment-2"><span>
                                        <div class="tab-element">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_ADVANCED') ?>
                                        </div>
                                    </span></a>
                            </li>
                        </ul>

                        <div class="t">

                            <div class="t">
                                <div class="t"></div>
                            </div>
                        </div>

                        <div class="m">

                            <div id="fragment-1">
                                <div>
                                    <br />
                                    <div class="bfFadingMessage" style="display:none"></div>
                                    <input type="submit" class="btn btn-secondary"
                                        value="<?php echo BFText::_('COM_BREEZINGFORMS_PROPERTIES_SAVE'); ?>"
                                        id="bfPropertySaveButtonTop" />
                                    <!-- FORM PROPERTIES BEGIN -->
                                    <div class="bfProperties" id="bfFormProperties" style="display:none">
                                        <br />
                                        <fieldset>

                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORM_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_TITLE')); ?>"
                                                    for="bfFormTitle">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_TITLE'); ?>
                                                </label>
                                                <input type="text"
                                                    value="<?php echo htmlentities($formTitle, ENT_QUOTES, 'UTF-8') ?>"
                                                    id="bfFormTitle" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_TITLE')); ?>"
                                                    for="bfFormTitleTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_TITLE'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="" id="bfFormTitleTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_NAME')); ?>"
                                                    for="bfFormName">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_NAME'); ?>
                                                </label>
                                                <input type="text"
                                                    value="<?php echo htmlentities($formName, ENT_QUOTES, 'UTF-8') ?>"
                                                    id="bfFormName" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_DESCRIPTION')); ?>"
                                                    for="bfFormDescription">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_DESC'); ?>
                                                </label>
                                                <textarea
                                                    id="bfFormDescription"><?php echo htmlentities($formDesc, ENT_QUOTES, 'UTF-8') ?></textarea>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_LASTPAGE')); ?>"
                                                    for="bfFormLastPageThankYou">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_LAST_PAGE_THANK_YOU'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfFormLastPageThankYou" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_EMAIL_NOTIFICATION')); ?>""
                                                       for=" bfFormMailNotification">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_MAIL_NOTIFICATION'); ?>
                                                </label>
                                                <input <?php echo $formEmailntf == 2 || $formEmailntf == 1 ? 'checked="checked"' : '' ?> type="checkbox"
                                                    value="<?php echo htmlentities($formEmailntf, ENT_QUOTES, 'UTF-8') ?>"
                                                    id="bfFormMailNotification" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_EMAIL_NOTIFICATION_ADDRESS')); ?>"
                                                    for="bfFormMailRecipient">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_MAIL_RECIPIENT'); ?>
                                                </label>
                                                <input type="text"
                                                    value="<?php echo htmlentities($formEmailadr, ENT_QUOTES, 'UTF-8') ?>"
                                                    id="bfFormMailRecipient" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_SUBMIT_INCLUDE')); ?>"
                                                    for="bfSubmitIncludeYes">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_SUBMIT_INCLUDE'); ?>
                                                </label>
                                                <input checked="checked" type="radio" name="bfSubmitInclude" value=""
                                                    id="bfSubmitIncludeYes" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                <input type="radio" name="bfSubmitInclude" value="" id="bfSubmitIncludeNo" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_SUBMIT_LABEL')); ?>"
                                                    for="bfFormSubmitLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_SUBMIT_LABEL'); ?>
                                                </label>
                                                <input type="text" value="save" id="bfFormSubmitLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_SUBMIT_LABEL')); ?>"
                                                    for="bfFormSubmitLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_SUBMIT_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="save" id="bfFormSubmitLabelTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_PAGING_INCLUDE')); ?>"
                                                    for="bfPagingIncludeYes">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_PAGING_INCLUDE'); ?>
                                                </label>
                                                <input checked="checked" type="radio" name="bfPagingInclude" value=""
                                                    id="bfPagingIncludeYes" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                <input type="radio" name="bfPagingInclude" value="" id="bfPagingIncludeNo" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_PAGING_NEXT_LABEL')); ?>"
                                                    for="bfFormPagingNextLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_PAGING_NEXT_LABEL'); ?>
                                                </label>
                                                <input type="text" value="next" id="bfFormPagingNextLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_PAGING_NEXT_LABEL')); ?>"
                                                    for="bfFormPagingNextLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_PAGING_NEXT_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="next" id="bfFormPagingNextLabelTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_PAGING_PREV_LABEL')); ?>"
                                                    for="bfFormPagingPrevLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_PAGING_PREV_LABEL'); ?>
                                                </label>
                                                <input type="text" value="back" id="bfFormPagingPrevLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_PAGING_PREV_LABEL')); ?>"
                                                    for="bfFormPagingPrevLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_PAGING_PREV_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="back" id="bfFormPagingPrevLabelTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_CANCEL_INCLUDE')); ?>"
                                                    for="bfCancelIncludeYes">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_CANCEL_INCLUDE'); ?>
                                                </label>
                                                <input checked="checked" type="radio" name="bfCancelInclude" value=""
                                                    id="bfCancelIncludeYes" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                <input type="radio" name="bfCancelInclude" value="" id="bfCancelIncludeNo" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_CANCEL_LABEL')); ?>"
                                                    for="bfFormCancelLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_CANCEL_LABEL'); ?>
                                                </label>
                                                <input type="text" value="reset" id="bfFormCancelLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FORM_CANCEL_LABEL')); ?>"
                                                    for="bfFormCancelLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_CANCEL_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="reset" id="bfFormCancelLabelTrans" />
                                            </div>

                                        </fieldset>
                                    </div>
                                    <!-- FORM PROPERTIES END -->

                                    <!-- PAGE PROPERTIES BEGIN -->
                                    <div class="bfProperties" id="bfPageProperties" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_PAGE_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAGE_INTRO')); ?>"
                                                    for="bfPageIntro">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_PAGE_INTRO'); ?>
                                                </label>

                                                <?php
                                                echo JHtmlBootstrap::renderModal(
                                                    'bfPropertiesIntroModal',
                                                    array(
                                                        'url' => "index.php?option=com_breezingforms&tmpl=component&act=quickmode_editor",
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a href="javascript:jQuery('#bfPropertiesIntroModal').modal('show');void(0);"
                                                    data-toggle="modal">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_EDIT_INTRO'); ?>
                                                </a>

                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAGE_INTRO')); ?>"
                                                    for="bfPageIntroTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_PAGE_INTRO'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>


                                                <?php
                                                echo JHtmlBootstrap::renderModal(
                                                    'bfPropertiesIntroModal2',
                                                    array(
                                                        'url' => "index.php?option=com_breezingforms&tmpl=component&act=quickmode_editor&active_language_code=" . $active_language_code,
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a href="javascript:jQuery('#bfPropertiesIntroModal2').modal('show');void(0);"
                                                    data-toggle="modal">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_EDIT_INTRO'); ?>
                                                </a>

                                            </div>
                                        </fieldset>
                                    </div>
                                    <!-- PAGE PROPERTIES END -->

                                    <!-- SECTION PROPERTIES BEGIN -->
                                    <div class="bfProperties" id="bfSectionProperties" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_SECTION_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SECTION_TYPE')); ?>"
                                                    for="bfSectionType">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SECTION_TYPE'); ?>
                                                </label>
                                                <select id="bfSectionType">
                                                    <option value="normal">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_NORMAL'); ?>
                                                    </option>
                                                    <option value="section">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FIELDSET'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SECTION_DISPLAY_TYPE')); ?>"
                                                    for="bfSectionDisplayType">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SECTION_DISPLAY_TYPE'); ?>
                                                </label>
                                                <select id="bfSectionDisplayType">
                                                    <option value="inline">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_INLINE'); ?>
                                                    </option>
                                                    <option value="breaks">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_BREAKS'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SECTION_TITLE')); ?>"
                                                    for="bfSectionTitle">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SECTION_TITLE'); ?>
                                                </label>
                                                <input type="text" value="" id="bfSectionTitle" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SECTION_TITLE')); ?>"
                                                    for="bfSectionTitleTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SECTION_TITLE'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="" id="bfSectionTitleTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SECTION_NAME')); ?>"
                                                    for="bfSectionName">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SECTION_NAME'); ?>
                                                </label>
                                                <input type="text" value="" id="bfSectionName" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SECTION_DESCRIPTION')); ?>"
                                                    for="bfSectionDescription">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SECTION_DESCRIPTION'); ?>
                                                </label>


                                                <?php
                                                echo JHtmlBootstrap::renderModal(
                                                    'bfPropertiesDescriptionModal',
                                                    array(
                                                        'url' => "index.php?option=com_breezingforms&tmpl=component&act=quickmode_editor",
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a
                                                    href="javascript:jQuery('#bfPropertiesDescriptionModal').modal('show');void(0);">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_EDIT_DESCRIPTION'); ?>
                                                </a>

                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SECTION_DESCRIPTION')); ?>"
                                                    for="bfSectionDescriptionTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SECTION_DESCRIPTION'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>

                                                <?php
                                                echo JHtmlBootstrap::renderModal(
                                                    'bfPropertiesDescriptionModal2',
                                                    array(
                                                        'url' => "index.php?option=com_breezingforms&tmpl=component&act=quickmode_editor&active_language_code=" . $active_language_code,
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a
                                                    href="javascript:jQuery('#bfPropertiesDescriptionModal').modal('show');void(0);">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_EDIT_DESCRIPTION'); ?>
                                                </a>


                                            </div>
                                        </fieldset>
                                    </div>
                                    <!-- SECTION PROPERTIES END -->

                                    <!-- ELEMENT PROPERTIES BEGIN -->
                                    <div class="bfProperties" id="bfElementProperties" style="display:none">
                                        <br />
                                        <fieldset>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TYPE')); ?>"
                                                    for="bfElementType">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_TYPE'); ?>
                                                </label>
                                                <select id="bfElementType">
                                                    <option value="">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_CHOOSE_ONE'); ?>
                                                    </option>
                                                    <option value="bfElementTypeText">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_TEXTFIELD'); ?>
                                                    </option>
                                                    <option value="bfElementTypeTextarea">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_TEXTAREA'); ?>
                                                    </option>
                                                    <option value="bfElementTypeRadioGroup">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_RADIO_GROUP'); ?>
                                                    </option>
                                                    <option value="bfElementTypeCheckboxGroup">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_CHECKBOX_GROUP'); ?>
                                                    </option>
                                                    <option value="bfElementTypeCheckbox">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_CHECKBOX'); ?>
                                                    </option>
                                                    <option value="bfElementTypeSelect">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_SELECT'); ?>
                                                    </option>
                                                    <option value="bfElementTypeFile">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FILE'); ?>
                                                    </option>
                                                    <option value="bfElementTypeSubmitButton">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_SUBMIT_BUTTON'); ?>
                                                    </option>
                                                    <option value="bfElementTypeHidden">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDDEN'); ?>
                                                    </option>
                                                    <option value="bfElementTypeSummarize">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_SUMMARIZE'); ?>
                                                    </option>
                                                    <option value="bfElementTypeCaptcha">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_CAPTCHA'); ?>
                                                    </option>
                                                    <option value="bfElementTypeReCaptcha">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ReCaptcha'); ?>
                                                    </option>
                                                    <option value="bfElementTypeCalendarResponsive">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_CALENDAR_RESPONSIVE'); ?>
                                                    </option>
                                                    <option value="bfElementTypeCalendar">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_CALENDAR'); ?>
                                                    </option>
                                                    <option value="bfElementTypePayPal">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_PAYPAL'); ?>
                                                    </option>
                                                    <option value="bfElementTypeStripe">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_STRIPE'); ?>
                                                    </option>
                                                    <option value="bfElementTypeSofortueberweisung">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_SOFORTUEBERWEISUNG'); ?>
                                                    </option>
                                                    <option value="bfElementTypeNumberInput">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_NUMBER_INPUT'); ?>
                                                    </option>
                                                    <option value="bfElementTypeSignature">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_SIGNATURE'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LABEL')); ?>"
                                                    for="bfElementLabel">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_LABEL'); ?>
                                                </label>
                                                <input type="text" value="" id="bfElementLabel" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LABEL')); ?>"
                                                    for="bfElementLabelTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_LABEL'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="" id="bfElementLabelTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_NAME')); ?>"
                                                    for="bfElementName">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_NAME'); ?>
                                                </label>
                                                <input type="text" value="" id="bfElementName" />
                                            </div>

                                            <!-- NUMBER INPUT BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeNumberInput" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip" title="<?php echo 'value'; ?>"
                                                        for="bfElementTypeNumberInputValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputValue" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo 'placeholder'; ?>"
                                                        for="bfElementTypeNumberInputPlaceholder">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputPlaceholder" />
                                                </div>
                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo 'translated placholder'; ?>"
                                                        for="bfElementTypeNumberInputPlaceholderTrans">
                                                        <?php echo 'translated placeholder'; ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputPlaceholderTrans" />
                                                </div>
                                                <?php
                                                // Icon for Textfield
                                                // This code is only for NEW THEME
                                        
                                                $dataTheme = Zend_Json::decode($dataObjectString);
                                                // echo '<pre>';
                                                // print_r($dataTheme);
                                                // echo '</pre>';
                                                if ($dataTheme['properties']['themebootstrapThemeEngine'] == 'bootstrap' && $dataTheme['properties']['themebootstrap'] == 'Azure') {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo 'icon element'; ?>"
                                                            for="bfElementTypeNumberInputIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeNumberInputIcon" />
                                                    </div>
                                                <?php } ?>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_SIZE')); ?>"
                                                        for="bfElementTypeNumberInputSize">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_SIZE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_MAXLENGTH')); ?>"
                                                        for="bfElementTypeNumberInputMaxLength">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_MAX_LENGTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeNumberInputMaxLength" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_HINT')); ?>"
                                                        for="bfElementTypeNumberInputHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeNumberInputHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_HINT')); ?>"
                                                        for="bfElementTypeNumberInputHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeNumberInputHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- NUMBER INPUT END -->

                                            <!-- HIDDEN BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeHidden" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDDEN_VALUE')); ?>"
                                                        for="bfElementTypeHiddenValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeHiddenValue" />
                                                </div>
                                            </div>
                                            <!-- HIDDEN END -->
                                            <!-- SUMMARIZE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSummarize" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_SUMMARIZE_CONNECTWITH')); ?>"
                                                        for="bfElementTypeSummarizeConnectWith">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_SUMMARIZE_CONNECT_WITH'); ?>
                                                    </label>
                                                    <select id="bfElementTypeSummarizeConnectWith">
                                                        <option value="">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_CHOOSE_ONE'); ?>
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_SUMMARIZE_EMPTY_MESSAGE')); ?>"
                                                        for="bfElementTypeSummarizeEmptyMessage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_EMPTY_MESSAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSummarizeEmptyMessage" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_SUMMARIZE_EMPTY_MESSAGE')); ?>"
                                                        for="bfElementTypeSummarizeEmptyMessageTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_EMPTY_MESSAGE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSummarizeEmptyMessageTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_SUMMARIZE_EMPTY_HIDE')); ?>"
                                                        for="bfElementTypeSummarizeHideIfEmpty">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HIDE_EMPTY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeSummarizeHideIfEmpty" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_SUMMARIZE_USE_LABEL')); ?>"
                                                        for="bfElementTypeSummarizeUseElementLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_USE_LABEL'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypeSummarizeUseElementLabel" />
                                                </div>
                                            </div>
                                            <!-- SUMMARIZE END -->
                                            <!-- TEXTFIELD BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeText" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_VALUE')); ?>"
                                                        for="bfElementTypeTextValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextValue" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_VALUE')); ?>"
                                                        for="bfElementTypeTextValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextValueTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_PLACEHOLDER')); ?>"
                                                        for="bfElementTypeTextPlaceholder">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextPlaceholder" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_PLACEHOLDER')); ?>"
                                                        for="bfElementTypeTextPlaceholderTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextPlaceholderTrans" />
                                                </div>
                                                <?php
                                                // Icon for Textfield
                                                // This code is only for NEW THEME
                                        
                                                $dataTheme = Zend_Json::decode($dataObjectString);
                                                // echo '<pre>';
                                                // print_r($dataTheme);
                                                // echo '</pre>';
                                                if (isset($dataTheme['properties']) && isset($dataTheme['properties']['themebootstrapThemeEngine']) && isset($dataTheme['properties']['themebootstrap']) && $dataTheme['properties']['themebootstrapThemeEngine'] == 'bootstrap' && $dataTheme['properties']['themebootstrap'] == 'Azure') {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ICON')); ?>"
                                                            for="bfElementTypeTextIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeTextIcon" />
                                                    </div>

                                                <?php } ?>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_SIZE')); ?>"
                                                        for="bfElementTypeTextSize">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_SIZE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_MAXLENGTH')); ?>"
                                                        for="bfElementTypeTextMaxLength">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_MAX_LENGTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextMaxLength" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_HINT')); ?>"
                                                        for="bfElementTypeTextHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeTextHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTFIELD_HINT')); ?>"
                                                        for="bfElementTypeTextHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeTextHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- TEXTFIELD END -->
                                            <!-- TEXTAREA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeTextarea" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTAREA_VALUE')); ?>"
                                                        for="bfElementTypeTextareaValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeTextareaValue"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTAREA_VALUE')); ?>"
                                                        for="bfElementTypeTextareaValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeTextareaValueTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTAREA_PLACEHOLDER')); ?>"
                                                        for="bfElementTypeTextareaPlaceholder">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaPlaceholder" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTAREA_PLACEHOLDER')); ?>"
                                                        for="bfElementTypeTextareaPlaceholderTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_TEXT_PLACEHOLDER'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaPlaceholderTrans" />
                                                </div>
                                                <?php
                                                // Icon for Textarea
                                                // This code is only for NEW THEME
                                        
                                                $dataTheme = Zend_Json::decode($dataObjectString);

                                                if (isset($dataTheme['properties']) && isset($dataTheme['properties']['themebootstrap']) && isset($dataTheme['properties']['themebootstrapThemeEngine']) && $dataTheme['properties']['themebootstrapThemeEngine'] == 'bootstrap' && $dataTheme['properties']['themebootstrap'] == 'Azure') {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ICON')); ?>"
                                                            for="bfElementTypeTextareaIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeTextareaIcon" />
                                                    </div>

                                                <?php } ?>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTAREA_WIDTH')); ?>"
                                                        for="bfElementTypeTextareaWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaWidth" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTAREA_HEIGHT')); ?>"
                                                        for="bfElementTypeTextareaHeight">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HEIGHT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaHeight" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTAREA_MAXLENGTH')); ?>"
                                                        for="bfElementTypeTextareaMaxLength">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_MAX_LENGTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeTextareaMaxLength" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTAREA_MAXLENGTH_SHOW')); ?>"
                                                        for="bfElementTypeTextareaMaxLengthShow">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_MAX_LENGTH_SHOW'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeTextareaMaxLengthShow" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TEXTAREA_HTML')); ?>"
                                                        for="bfElementTypeTextareaIsHtml">HTML</label>
                                                    <input type="checkbox" value="" id="bfElementTypeTextareaIsHtml" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeTextareaHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeTextareaHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeTextareaHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeTextareaHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- TEXTAREA END -->

                                            <!-- RADIOGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeRadioGroup" style="display:none">

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_RADIO_GROUP')); ?>"
                                                        for="bfElementTypeRadioGroupGroups">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_GROUP'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeRadioGroupGroups"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_RADIO_GROUP')); ?>"
                                                        for="bfElementTypeRadioGroupGroupsTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_GROUP'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeRadioGroupGroupsTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_RADIO_GROUP_READONLY')); ?>"
                                                        for="bfElementTypeRadioGroupReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeRadioGroupReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_RADIO_GROUP_WRAP')); ?>"
                                                        for="bfElementTypeRadioGroupWrap">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_WRAP'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeRadioGroupWrap" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeRadioGroupHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeRadioGroupHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeRadioGroupHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeRadioGroupHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- RADIOGROUP END -->
                                            <!-- SUBMITBUTTON BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSubmitButton" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SUBMIT_VALUE')); ?>"
                                                        for="bfElementTypeSubmitButtonValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSubmitButtonValue" />
                                                </div>


                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SUBMIT_VALUE')); ?>"
                                                        for="bfElementTypeSubmitButtonValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSubmitButtonValueTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSubmitButtonHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSubmitButtonHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSubmitButtonHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSubmitButtonHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- SUBMITBUTTON END -->


                                            <!-- STRIPE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeStripe" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_SECRETKEY')); ?>"
                                                        for="bfElementTypeStripeSecretKey">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_SECRETKEY'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeSecretKey" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_PUBLISHABLEKEY')); ?>"
                                                        for="bfElementTypeStripePublishableKey">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_PUBLISHABLEKEY'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripePublishableKey" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_ITEMNAME')); ?>"
                                                        for="bfElementTypeStripeItemname">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_ITEMNAME'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeItemname" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_AMOUNT')); ?>"
                                                        for="bfElementTypeStripeAmount">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_AMOUNT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeAmount" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_CURRENCY')); ?>"
                                                        for="bfElementTypeStripeCurrencyCode">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_CURRENCY_CODE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeCurrencyCode" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_THANKYOUPAGE')); ?>"
                                                        for="bfElementTypeStripeThankYouPage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_THANKYOU_PAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeStripeThankYouPage" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_SENDNOTAFPAY')); ?>"
                                                        for="bfElementTypeStripeSendNotificationAfterPayment">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_NOTIFICATION_AFTER_PAYMENT'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypeStripeSendNotificationAfterPayment" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeStripeHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeStripeHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeStripeHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeStripeHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- STRIPE END -->


                                            <!-- PAYPAL BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypePayPal" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_BUSINESS')); ?>"
                                                        for="bfElementTypePayPalBusiness">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_BUSINESS'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalBusiness" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_TOKEN')); ?>"
                                                        for="bfElementTypePayPalToken">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_TOKEN'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalToken" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_ITEMNAME')); ?>"
                                                        for="bfElementTypePayPalItemname">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_ITEMNAME'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalItemname" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_ITEMNUMBER')); ?>"
                                                        for="bfElementTypePayPalItemnumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_ITEMNUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalItemnumber" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_AMOUNT')); ?>"
                                                        for="bfElementTypePayPalAmount">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_AMOUNT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalAmount" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_TAX')); ?>"
                                                        for="bfElementTypePayPalTax">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_TAX'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalTax" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_THANKYOUPAGE')); ?>"
                                                        for="bfElementTypePayPalThankYouPage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_THANKYOU_PAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalThankYouPage" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_CANCEL')); ?>"
                                                        for="bfElementTypePayPalCancelURL">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_CANCEL_URL'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalCancelURL" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_LOCALE')); ?>"
                                                        for="bfElementTypePayPalLocale">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_LOCALE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalLocale" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_CURRENCY')); ?>"
                                                        for="bfElementTypePayPalCurrencyCode">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_CURRENCY_CODE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypePayPalCurrencyCode" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_SENDNOTAFPAY')); ?>"
                                                        for="bfElementTypePayPalSendNotificationAfterPayment">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_NOTIFICATION_AFTER_PAYMENT'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypePayPalSendNotificationAfterPayment" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypePayPalHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypePayPalHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypePayPalHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypePayPalHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- PAYPAL END -->
                                            <!-- SOFORTUEBERWEISUNG BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSofortueberweisung"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_USERID')); ?>"
                                                        for="bfElementTypeSofortueberweisungUserId">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_USERID'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungUserId" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_PROJECTID')); ?>"
                                                        for="bfElementTypeSofortueberweisungProjectId">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_PROJECTID'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungProjectId" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_PROJECTPASSWORD')); ?>"
                                                        for="bfElementTypeSofortueberweisungProjectPassword">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_PROJECT_PASSWORD'); ?>
                                                    </label>
                                                    <input type="password" value=""
                                                        id="bfElementTypeSofortueberweisungProjectPassword" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_REASON1')); ?>"
                                                        for="bfElementTypeSofortueberweisungReason1">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_REASON1'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungReason1" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_REASON2')); ?>"
                                                        for="bfElementTypeSofortueberweisungReason2">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_REASON2'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungReason2" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_AMOUNT')); ?>"
                                                        for="bfElementTypeSofortueberweisungAmount">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_AMOUNT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSofortueberweisungAmount" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_THANKYOUPAGE')); ?>"
                                                        for="bfElementTypeSofortueberweisungThankYouPage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_THANKYOU_PAGE'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeSofortueberweisungThankYouPage" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_LANGUAGEID')); ?>"
                                                        for="bfElementTypeSofortueberweisungLanguageId">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_LANGUAGE_ID'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeSofortueberweisungLanguageId" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_CURRENCY')); ?>"
                                                        for="bfElementTypeSofortueberweisungCurrencyId">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_CURRENCY_ID'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeSofortueberweisungCurrencyId" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_SENDNOTAFPAY')); ?>"
                                                        for="bfElementTypeSofortueberweisungSendNotificationAfterPayment">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_NOTIFICATION_AFTER_PAYMENT'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypeSofortueberweisungSendNotificationAfterPayment" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_MAILBACK')); ?>"
                                                        for="bfElementTypeSofortueberweisungMailback">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_MAILBACK'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementTypeSofortueberweisungMailback" />
                                                </div>
                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSofortueberweisungHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSofortueberweisungHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSofortueberweisungHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSofortueberweisungHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- SOFORTUEBERWEISUNG END -->
                                            <!-- CAPTCHA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCaptcha" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCaptchaHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCaptchaHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCaptchaHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCaptchaHintTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CAPTCHA_WIDTH')); ?>"
                                                        for="bfElementTypeCaptchaWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_CAPTCHA_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCaptchaWidth" />
                                                </div>
                                            </div>
                                            <!-- CAPTCHA END -->
                                            <!-- RECAPTCHA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeReCaptcha" style="display:none">

                                                <div class="bfPropertyWrap">
                                                    <label for="bfElementTypeReCaptchaNew" class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_RECAPTCHA_NEW_CAPTCHA_HINT')); ?>">reCaptcha</label>

                                                    <label>
                                                        <input type="radio" name="bfElementTypeReCaptchaNew" value=""
                                                            id="bfElementTypeReCaptchaNew" checked="checked" />
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_QM_RECAPTCHA_NEW_CAPTCHA'); ?>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="bfElementTypeReCaptchaNew" value=""
                                                            id="bfElementTypeReCaptchaInvisible" />
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_QM_RECAPTCHA_INVISIBLE_CAPTCHA'); ?>
                                                    </label>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_RECAPTCHA_PUBKEY')); ?>"
                                                        for=bfElementTypeReCaptchaPubkey><?php echo BFText::_('COM_BREEZINGFORMS_PUBLIC_KEY'); ?></label>
                                                    <input type="text" value="" id="bfElementTypeReCaptchaPubkey" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_RECAPTCHA_PRIVKEY')); ?>"
                                                        for=bfElementTypeReCaptchaPrivkey><?php echo BFText::_('COM_BREEZINGFORMS_PRIVATE_KEY'); ?></label>
                                                    <input type="text" value="" id="bfElementTypeReCaptchaPrivkey" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_RECAPTCHA_THEME')); ?>"
                                                        for=bfElementTypeReCaptchaTheme><?php echo BFText::_('COM_BREEZINGFORMS_Theme'); ?></label>
                                                    <input type="text" value="red" id="bfElementTypeReCaptchaTheme" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_RECAPTCHA_SIZE')); ?>"
                                                        for=bfElementTypeReCaptchaSize><?php echo BFText::_('COM_BREEZINGFORMS_RECAPTCHA_SIZE'); ?></label>
                                                    <input type="text" value="normal" id="bfElementTypeReCaptchaSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeReCaptchaHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeReCaptchaHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeReCaptchaHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeReCaptchaHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- RECAPTCHA END -->
                                            <!-- CALENDAR Responsive BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCalendarResponsive"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_FORMAT')); ?>"
                                                        for="bfElementTypeCalendarResponsiveFormat">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_FORMAT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarResponsiveFormat" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_FORMAT')); ?>"
                                                        for="bfElementTypeCalendarResponsiveFormatTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_FORMAT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeCalendarResponsiveFormatTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_VALUE')); ?>"
                                                        for="bfElementTypeCalendarResponsiveValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarResponsiveValue" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_VALUE')); ?>"
                                                        for="bfElementTypeCalendarResponsiveValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementTypeCalendarResponsiveValueTrans" />
                                                </div>
                                                <?php
                                                // Icon for Responsive Calendar
                                                // This code is only for NEW THEME
                                        
                                                $dataTheme = Zend_Json::decode($dataObjectString);

                                                if ($dataTheme['properties']['themebootstrapThemeEngine'] == 'bootstrap' && $dataTheme['properties']['themebootstrap'] == 'Azure') {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ICON')); ?>"
                                                            for="bfElementTypeCalendarResponsiveIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeCalendarResponsiveIcon" />
                                                    </div>

                                                <?php } ?>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_SIZE')); ?>"
                                                        for="bfElementTypeCalendarResponsiveSize">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_SIZE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarResponsiveSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCalendarResponsiveHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCalendarResponsiveHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCalendarResponsiveHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCalendarResponsiveHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- CALENDAR RESPONSIVE END -->
                                            <!-- CALENDAR BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCalendar" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_FORMAT')); ?>"
                                                        for="bfElementTypeCalendarFormat">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_FORMAT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarFormat" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_FORMAT')); ?>"
                                                        for="bfElementTypeCalendarFormatTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_FORMAT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarFormatTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_VALUE')); ?>"
                                                        for="bfElementTypeCalendarValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarValue" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_VALUE')); ?>"
                                                        for="bfElementTypeCalendarValueTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarValueTrans" />
                                                </div>

                                                <?php
                                                // Icon for Calendar
                                                // This code is only for NEW THEME
                                        
                                                $dataTheme = Zend_Json::decode($dataObjectString);

                                                if ($dataTheme['properties']['themebootstrapThemeEngine'] == 'bootstrap' && $dataTheme['properties']['themebootstrap'] == 'Azure') {
                                                    ?>

                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ICON')); ?>"
                                                            for="bfElementTypeCalendarIcon">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_ICON'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementTypeCalendarIcon" />
                                                    </div>

                                                <?php } ?>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_SIZE')); ?>"
                                                        for="bfElementTypeCalendarSize">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_SIZE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeCalendarSize" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCalendarHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCalendarHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCalendarHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCalendarHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- CALENDAR END -->
                                            <!-- SIGNATURE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSignature" style="display:none">

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSignatureHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSignatureHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSignatureHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSignatureHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- SIGNATURE END -->
                                            <!-- CHECKBOXGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCheckboxGroup"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CHECKBOX_GROUP')); ?>"
                                                        for="bfElementTypeCheckboxGroupGroups">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_GROUP'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxGroupGroups"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CHECKBOX_GROUP')); ?>"
                                                        for="bfElementTypeCheckboxGroupGroupsTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_GROUP'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxGroupGroupsTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CHECKBOX_GROUP_READONLY')); ?>"
                                                        for="bfElementTypeCheckboxGroupReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeCheckboxGroupReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CHECKBOX_GROUP_WRAP')); ?>"
                                                        for="bfElementTypeCheckboxGroupWrap">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_WRAP'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeCheckboxGroupWrap" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCheckboxGroupHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxGroupHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCheckboxGroupHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxGroupHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- CHECKBOXGROUP END -->
                                            <!-- CHECKBOX BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCheckbox" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_CHECKBOX_VALUE')); ?>"
                                                        for="bfElementTypeCheckboxValue">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALUE'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxValue"></textarea>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_CHECKBOX_CHECKED')); ?>"
                                                        for="bfElementTypeCheckboxChecked">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_CHECKED'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeCheckboxChecked" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_CHECKBOX_READONLY')); ?>"
                                                        for="bfElementTypeCheckboxReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeCheckboxReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCheckboxHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeCheckboxHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeCheckboxHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- CHECKBOX END -->
                                            <!-- SELECT BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSelect" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SELECTLIST')); ?>"
                                                        for="bfElementTypeSelectList">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_LIST'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSelectList"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SELECTLIST')); ?>"
                                                        for="bfElementTypeSelectListTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_LIST'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSelectListTrans"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SELECTLIST_MULTIPLE')); ?>"
                                                        for="bfElementTypeSelectMultiple">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_MULTIPLE'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeSelectMultiple" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SELECTLIST_WIDTH')); ?>"
                                                        for="bfElementTypeSelectListWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSelectListWidth" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SELECTLIST_HEIGHT')); ?>"
                                                        for="bfElementTypeSelectListHeight">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HEIGHT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTypeSelectListHeight" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SELECTLIST_READONLY')); ?>"
                                                        for="bfElementTypeSelectReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeSelectReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSelectHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeSelectHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeSelectHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeSelectHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- SELECT END -->
                                            <!-- FILE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeFile" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_READONLY')); ?>"
                                                        for="bfElementTypeFileReadonly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementTypeFileReadonly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeFileHint">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                    </label>
                                                    <textarea id="bfElementTypeFileHint"></textarea>
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HINT')); ?>"
                                                        for="bfElementTypeFileHintTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_HINT'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <textarea id="bfElementTypeFileHintTrans"></textarea>
                                                </div>
                                            </div>
                                            <!-- FILE END -->
                                        </fieldset>
                                        <fieldset id="bfValidationScript" style="display:none">
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALIDATION'); ?>
                                            </legend>
                                            <span id="bfElementValidationRequiredSet" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_VALIDATION_REQUIRED')); ?>"
                                                        for="bfElementValidationRequired">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALIDATION_REQUIRED'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementValidationRequired" />
                                                </div>
                                            </span>

                                            <div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_VALIDATION_TYPE')); ?>"
                                                        for="bfElementValidation">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_VALIDATION_LABEL'); ?>
                                                    </label>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_TYPE') ?>:
                                                    <input
                                                        onclick="JQuery('#bfValidationScriptFlags').css('display', 'none');
                                                                            JQuery('#bfValidationScriptLibrary').css('display', 'none');JQuery('#bfValidationScriptCustom').css('display', 'none');"
                                                        type="radio" name="validationType" id="bfValidationTypeNone"
                                                        class="bfValidationType" value="0" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_NONE') ?>
                                                    <input
                                                        onclick="JQuery('#bfValidationScriptFlags').css('display', '');
                                                                            JQuery('#bfValidationScriptLibrary').css('display', '');JQuery('#bfValidationScriptCustom').css('display', 'none');"
                                                        type="radio" name="validationType" id="bfValidationTypeLibrary"
                                                        class="bfValidationType" value="1" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_LIBRARY') ?>
                                                    <input
                                                        onclick="JQuery('#bfValidationScriptFlags').css('display', '');JQuery('#bfValidationScriptLibrary').css('display', 'none');
                                                                            JQuery('#bfValidationScriptCustom').css('display', '');"
                                                        type="radio" name="validationType" id="bfValidationTypeCustom"
                                                        class="bfValidationType" value="2" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_CUSTOM') ?>
                                                </div>

                                                <div id="bfValidationScriptFlags" style="display:none">
                                                    <hr />
                                                    <div class="bfPropertyWrap">
                                                        <span class="hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_VALIDATION_ERROR_MESSAGE')); ?>">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_ERROR_MESSAGE') ?>
                                                            :
                                                        </span> <input type="text" style="width:100%" maxlength="255"
                                                            class="bfValidationMessage" id="bfValidationMessage"
                                                            name="bfValidationMessage" value="" class="inputbox" />
                                                    </div>

                                                    <div class="bfPropertyWrap bfTrans">
                                                        <span class="hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_VALIDATION_ERROR_MESSAGE')); ?>">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_ERROR_MESSAGE') ?>
                                                            <em>(
                                                                <?php echo $active_language_code ?>)
                                                            </em>:
                                                        </span>
                                                        <input type="text" style="width:100%" maxlength="255"
                                                            class="bfValidationMessage" id="bfValidationMessageTrans"
                                                            name="bfValidationMessage" value="" class="inputbox" />
                                                    </div>
                                                </div>

                                                <div id="bfValidationScriptLibrary" style="display:none">
                                                    <hr />
                                                    <div class="bfPropertyWrap">
                                                        <span class="hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_VALIDATION_SCRIPTLIBRARY')); ?>">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_SCRIPT') ?>
                                                            :
                                                        </span><br /> <select id="bfValidationScriptSelection"></select>
                                                    </div>
                                                    <br />
                                                    <div id="bfValidationScriptSelectionDescription"></div>
                                                </div>

                                                <div id="bfValidationScriptCustom" style="display:none">
                                                    <hr />
                                                    <div class="bfPropertyWrap">
                                                        <div class="hasTooltip btn btn-secondary"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_VALIDATION_CODEFRAMEWORK')); ?>"
                                                            style="cursor: pointer;" onclick="createValidationCode()">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_CREATE_CODE_FRAMEWORK') ?>
                                                        </div>

                                                        <?php
                                                        echo self::renderCodeMirror('bfValidationCode');
                                                        ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <br />
                                    <!-- ELEMENT PROPERTIES END -->
                                    <div class="bfFadingMessage" style="display:none"></div>
                                    <input type="submit" class="btn btn-secondary"
                                        value="<?php echo BFText::_('COM_BREEZINGFORMS_PROPERTIES_SAVE'); ?>"
                                        id="bfPropertySaveButton" />
                                    <br />
                                    <br />

                                </div>
                            </div>

                            <div id="fragment-2">
                                <div>
                                    <br />

                                    <div class="bfFadingMessage" style="display:none"></div>
                                    <input type="submit" class="btn btn-secondary"
                                        value="<?php echo BFText::_('COM_BREEZINGFORMS_PROPERTIES_SAVE'); ?>"
                                        id="bfAdvancedSaveButtonTop" />
                                    <div class="bfAdvanced" id="bfPageAdvanced" style="display:none">
                                    </div>
                                    <div class="bfAdvanced" id="bfFormAdvanced" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_ADVANCED_FORM_OPTIONS'); ?>
                                            </legend>
                                            <?php if ($formId != 0) { ?>
                                                <a href="index.php?option=com_breezingforms&task=editform&act=editpage&form=<?php echo $formId ?>&pkg=QuickModeForms"
                                                    title="<?php echo BFText::_('COM_BREEZINGFORMS_MORE_OPTIONS'); ?>">
                                                    <?php echo htmlentities(BFText::_('COM_BREEZINGFORMS_MORE_OPTIONS'), ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            <?php } ?>
                                        </fieldset>
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_ADVANCED_FORM_THEMES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_ENGINE')); ?>"
                                                    for="bfThemeBootstrapThemeBreezingForms">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_CHOOSE_THEME_ENGINE'); ?>
                                                </label>
                                                <input
                                                    onclick="JQuery('#bfThemeBootstrapDiv').css('display', 'none');JQuery('#bfThemeBreezingFormsDiv').css('display', 'block');" type="radio"
                                                    name="bfThemeBootstrapThemeEngine" value=""
                                                    id="bfThemeBootstrapThemeBreezingForms" /> BreezingForms
                                                <input
                                                    onclick="JQuery('#bfThemeBootstrapDiv').css('display', 'block'); JQuery('#bfThemeBreezingFormsDiv').css('display', 'none');" 
                                                    type="radio"
                                                    name="bfThemeBootstrapThemeEngine" value=""
                                                    id="bfThemeBootstrapThemeBootstrap" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_THEME_ENGINE_BOOTSTRAP'); ?>
                                            </div>
                                            <div id="bfThemeBreezingFormsDiv" style="display:none;">
                                                <br />
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_ADVANCED_THEME_BREEZINGFORMS_ENGINE'); ?>
                                                </legend>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_NATIVE')); ?>"
                                                        for="bfTheme">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_THEME'); ?>
                                                    </label>
                                                    <select id="bfTheme">
                                                        <?php
                                                        $tCount = count($themes);
                                                        for ($i = 0; $i < $tCount; $i++) {
                                                            echo '<option value="' . $themes[$i] . '">' . $themes[$i] . '</option>' . "\n";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div id="bfThemeBootstrapDiv" style="display:none;">
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_ADVANCED_THEME_BOOTSTRAP_ENGINE'); ?>
                                                </legend>
                                                <br />
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP')); ?>"
                                                        for="bfThemeBootstrap">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_THEME_BOOTSTRAP'); ?>
                                                    </label>
                                                    <?php
                                                    $dbObject = Zend_Json::decode($dataObjectString);
                                                    $useBs3 = false;
                                                    if (isset($dbObject['properties']['themebootstrapUse3']) && $dbObject['properties']['themebootstrapUse3']) {
                                                        $useBs3 = true;
                                                    }
                                                    ?>
                                                    <select id="bfThemeBootstrap" <?php echo $useBs3 ? ' style="display: none;"' : ''; ?>>
                                                        <option value="">Default</option>
                                                        >
                                                        <?php
                                                        $tCount = count($themesbootstrap);
                                                        for ($i = 0; $i < $tCount; $i++) {
                                                            echo '<option value="' . $themesbootstrap[$i] . '">' . $themesbootstrap[$i] . '</option>' . "\n";
                                                        }
                                                        ?>
                                                    </select>
                                                    <select id="bfThemeBootstrap3" <?php echo !$useBs3 ? ' style="display: none;"' : ''; ?>>
                                                        <option value="">Default</option>
                                                        >
                                                        <?php
                                                        $tCount = count($themesbootstrap3);
                                                        for ($i = 0; $i < $tCount; $i++) {
                                                            echo '<option value="' . $themesbootstrap3[$i] . '">' . $themesbootstrap3[$i] . '</option>' . "\n";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
  
                                                <div class="bfPropertyWrap" style="display:none;">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_USE_BOOTSTRAP3_TIP')); ?>"
                                                        for="bfThemeBootstrapUse3Yes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_QM_THEME_USE_BOOTSTRAP3'); ?>
                                                    </label>

                                                    <input
                                                        onclick="JQuery('#bfThemeBootstrap3').css('display', 'block');
                                                                                        JQuery('#bfThemeBootstrap').css('display', 'none');"
                                                        type="radio" name="bfThemeBootstrapUse3" value=""
                                                        id="bfThemeBootstrapUse3Yes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                    <input checked="checked"
                                                        onclick="JQuery('#bfThemeBootstrap').css('display', 'block');
                                                                                        JQuery('#bfThemeBootstrap3').css('display', 'none');"
                                                        type="radio" name="bfThemeBootstrapUse3" value=""
                                                        id="bfThemeBootstrapUse3No" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                                </div>

                                                <div class="bfPropertyWrap" style="display:none;">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP3_BUILTIN_TIP')); ?>"
                                                        for="bfThemeBootstrap3BuiltInYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP3_BUILTIN'); ?>
                                                    </label>

                                                    <input checked="checked" type="radio" name="bfThemeBootstrap3BuiltIn"
                                                        value="" id="bfThemeBootstrap3BuiltInYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                    <input type="radio" name="bfThemeBootstrap3BuiltIn" value=""
                                                        id="bfThemeBootstrap3BuiltInNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                                </div>

                                                <div class="bfPropertyWrap" style="display:none;">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP3_CLASSPFX_TIP')); ?>"
                                                        for="bfThemeBootstrap3Classpfx">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP3_CLASSPFX'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfThemeBootstrap3Classpfx" />
                                                </div>


                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP_MODE_TIP')); ?>"
                                                        for="bfThemeBootstrapModeYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP_MODE'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapMode" value=""
                                                        id="bfThemeBootstrapModeYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapMode" value=""
                                                        id="bfThemeBootstrapModeNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP_LABEL_TOP')); ?>"
                                                        for="bfThemeBootstrapLabelTopYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_THEME_BOOTSTRAP_LABELTOP'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapLabelTop" value=""
                                                        id="bfThemeBootstrapLabelTopYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapLabelTop"
                                                        value="" id="bfThemeBootstrapLabelTopNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP_HERO_UNIT')); ?>"
                                                        for="bfThemeBootstrapUseHeroUnitYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_THEME_BOOTSTRAP_USE_HERO_UNIT'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapUseHeroUnit" value=""
                                                        id="bfThemeBootstrapUseHeroUnitYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapUseHeroUnit"
                                                        value="" id="bfThemeBootstrapUseHeroUnitNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP_WELL')); ?>"
                                                        for="bfThemeBootstrapUseWellYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_THEME_BOOTSTRAP_USE_WELL'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapUseWell" value=""
                                                        id="bfThemeBootstrapUseWellYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapUseWell"
                                                        value="" id="bfThemeBootstrapUseWellNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP_PROGRESS')); ?>"
                                                        for="bfThemeBootstrapUseProgressYes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_THEME_BOOTSTRAP_USE_PROGRESS'); ?>
                                                    </label>

                                                    <input type="radio" name="bfThemeBootstrapUseProgress" value=""
                                                        id="bfThemeBootstrapUseProgressYes" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                                    <input checked="checked" type="radio" name="bfThemeBootstrapUseProgress"
                                                        value="" id="bfThemeBootstrapUseProgressNo" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                                </div>

                                                <?php
                                                jimport('joomla.filesystem.file');
                                                jimport('joomla.filesystem.folder');
                                                $dbObject = Zend_Json::decode($dataObjectString);
                                                if (isset($dbObject['properties']['themebootstrap'])) {
                                                    $themeboostrapfolder = $dbObject['properties']['themebootstrap'];
                                                    $folder = 'themes-bootstrap';
                                                    if (isset($dbObject['properties']['themebootstrapUse3']) && $dbObject['properties']['themebootstrapUse3']) {
                                                        $folder = 'themes-bootstrap3';
                                                    }
                                                    $themesbootstrap_path = JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . $folder . DS . $themeboostrapfolder . DS;
                                                    if (is_dir($themesbootstrap_path) && file_exists($themesbootstrap_path . 'vars.txt')) {
                                                        $varscontent = htmlentities(BFFile::read($themesbootstrap_path . 'vars.txt'), ENT_QUOTES, 'UTF-8');
                                                        if ($varscontent) {
                                                            echo '<br/>
                                                                <div class="bfPropertyWrap">
                                                                <label class="bfPropertyLabel hasTooltip" title="' . bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_THEME_BOOTSTRAP_PROGRESS')) . '" for="bfThemeBootstrapVars">' . BFText::_('COM_BREEZINGFORMS_THEME_BOOTSTRAP_VARS') . '</label>
                                                                <textarea id="bfThemeBootstrapVars">' . $varscontent . '</textarea>
                                                                </div>
                                                                <input type="hidden" name="bfThemeBootstrapBefore" id="bfThemeBootstrapBefore" value=""/>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
 
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_ADVANCED_FORM_OTHER'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_MOBILE_FORMS')); ?>"
                                                    for="bfElementAdvancedMobileEnabled">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_MOBILE_ENABLED'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedMobileEnabled" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_MOBILE_FORMS_FORCE')); ?>"
                                                    for="bfElementAdvancedForceMobile">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORCE_MOBILE'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedForceMobile" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_MOBILE_FORMS_URL')); ?>"
                                                    for="bfElementAdvancedForceMobileUrl">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORCE_MOBILE_URL'); ?>
                                                </label>
                                                <input type="text" value="" id="bfElementAdvancedForceMobileUrl" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_HINT_JOOMLA')); ?>"
                                                    for="bfElementAdvancedJoomlaHint">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_JOOMLA_HINT'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedJoomlaHint" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_JQUERY_DISABLE')); ?>"
                                                    for="bfElementAdvancedDisableJQuery">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_DISABLE_JQUERY'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedDisableJQuery" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ERROR_ALERTS')); ?>"
                                                    for="bfElementAdvancedUseErrorAlerts">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_USE_ERROR_ALERTS'); ?>
                                                </label>
                                                <input type="checkbox" value="" id="bfElementAdvancedUseErrorAlerts" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ERROR_DEFAULT')); ?>"
                                                    for="bfElementAdvancedUseDefaultErrors">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_IF_NOT_USE_ERROR_ALERTS'); ?>
                                                </label>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_IF_USE_DEFAULT_ERRORS'); ?>
                                                <input type="checkbox" value="" id="bfElementAdvancedUseDefaultErrors" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_IF_USE_BALLOON_ERRORS'); ?>
                                                <input type="checkbox" value="" id="bfElementAdvancedUseBalloonErrors" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <div id="bfFadingEffectToggle">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FADE_IN')); ?>"
                                                        for="bfElementAdvancedFadeIn">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FADE_IN'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedFadeIn" />
                                                </div>
                                            </div>
                                            <div id="bfRollOverToggle">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ROLLOVER')); ?>"
                                                        for="bfElementAdvancedRollover">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ROLLOVER'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedRollover" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ROLLOVER_COLOR')); ?>"
                                                        for="bfElementAdvancedRolloverColor">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ROLLOVER_COLOR'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementAdvancedRolloverColor" />
                                                </div>

                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_VISIBILITY_RULES')); ?>"
                                                    for="bfElementAdvancedToggleFields">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORM_TOGGLEFIELDS'); ?>
                                                </label>
                                                <textarea id="bfElementAdvancedToggleFields"></textarea>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="bfAdvanced" id="bfSectionAdvanced" style="display:none">
                                        <div class="bfPropertyWrap">
                                            <label class="bfPropertyLabel hasTooltip"
                                                title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SECTION_TURNOFF')); ?>"
                                                for="bfSectionAdvancedTurnOff">
                                                <?php echo BFText::_('COM_BREEZINGFORMS_TURN_OFF_INITIALLY'); ?>
                                            </label>
                                            <input type="checkbox" value="" id="bfSectionAdvancedTurnOff" />
                                        </div>
                                    </div>
                                    <div class="bfAdvanced" id="bfElementAdvanced" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_ADVANCED_ELEMENT_OPTIONS'); ?>
                                            </legend>
                                            <div></div>
                                            <!-- HIDDEN BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeHiddenAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementHiddenAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementHiddenAdvancedOrderNumber" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementHiddenAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementHiddenAdvancedLogging" />
                                                </div>
                                            </div>
                                            <!-- HIDDEN END -->

                                            <!-- NUMBER INPUT BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeNumberInputAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementNumberInputAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementNumberInputAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementNumberInputAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementNumberInputAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_NUMBER_INPUT_STEP_TOOLTIP')); ?>"
                                                        for="bfElementNumberInputAdvancedStep">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_QM_NUMBER_INPUT_STEP'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementNumberInputAdvancedStep" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_NUMBER_INPUT_MIN_TOOLTIP')); ?>"
                                                        for="bfElementNumberInputAdvancedMin">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_QM_NUMBER_INPUT_MIN'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementNumberInputAdvancedMin" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_NUMBER_INPUT_MAX_TOOLTIP')); ?>"
                                                        for="bfElementNumberInputAdvancedMax">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_QM_NUMBER_INPUT_MAX'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementNumberInputAdvancedMax" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementNumberInputOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementNumberInputOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- NUMBER INPUT END -->
                                            <!--  SUMMARIZE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSummarizeAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SUMMARIZE_CALC')); ?>"
                                                        for="bfElementAdvancedSummarizeCalc">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENT_CALC'); ?>
                                                    </label>
                                                    <textarea id="bfElementAdvancedSummarizeCalc"></textarea>
                                                </div>
                                            </div>
                                            <!--  SUMMARIZE END -->

                                            <!-- TEXTFIELD BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeTextAdvanced" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_TEXTFIELD_PASSWORD')); ?>"
                                                        for="bfElementAdvancedPassword">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_PASSWORD'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedPassword" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_TEXTFIELD_READONLY')); ?>"
                                                        for="bfElementAdvancedReadOnly">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_READONLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedReadOnly" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_TEXTFIELD_MAILBACK')); ?>"
                                                        for="bfElementAdvancedMailback">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedMailback" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_TEXTFIELD_MAILBACK_AS_SENDER')); ?>"
                                                        for="bfElementAdvancedMailbackAsSender">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK_AS_SENDER'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedMailbackAsSender" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_TEXTFIELD_MAILBACK_FILE')); ?>"
                                                        for="bfElementAdvancedMailbackfile">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_MAILBACKFILE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementAdvancedMailbackfile" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- TEXTFIELD END -->
                                            <!-- TEXTAREA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeTextareaAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementTextareaAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementTextareaAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementTextareaAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementTextareaAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementTextareaAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementTextareaAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- TEXTAREA END -->

                                            <!-- RADIOGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeRadioGroupAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementRadioGroupAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementRadioGroupAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementRadioGroupAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementRadioGroupAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementRadioGroupAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementRadioGroupAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- RADIOGROUP END -->
                                            <!-- SUBMITBUTTON BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSubmitButtonAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementSubmitButtonAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSubmitButtonAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SUBMIT_BUTTON_SOURCE')); ?>"
                                                        for="bfElementSubmitButtonAdvancedSrc">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_SOURCE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSubmitButtonAdvancedSrc" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SUBMIT_BUTTON_SOURCE')); ?>"
                                                        for="bfElementSubmitButtonAdvancedSrcTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_SOURCE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSubmitButtonAdvancedSrcTrans" />
                                                </div>
                                            </div>
                                            <!-- SUBMITBUTTON END -->


                                            <!-- STRIPE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeStripeAdvanced"
                                                style="display:none">


                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_IMAGE')); ?>"
                                                        for="bfElementStripeAdvancedImage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_IMAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedImage" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_IMAGE')); ?>"
                                                        for="bfElementStripeAdvancedImageTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_IMAGE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedImageTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementStripeAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementStripeAdvancedHideLabel" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_FILE')); ?>"
                                                        for="bfElementStripeAdvancedDownloadableFile">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_DOWNLOADABLE_FILE'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementStripeAdvancedDownloadableFile" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_FILEPATH')); ?>"
                                                        for="bfElementStripeAdvancedFilepath">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FILEPATH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedFilepath" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_TRIES')); ?>"
                                                        for="bfElementStripeAdvancedDownloadTries">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_DOWNLOAD_TRIES'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedDownloadTries" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltiptext(BFText::_('COM_BREEZINGFORMS_QM_STRIPE_EMAIL')); ?>"
                                                        for="bfElementStripeAdvancedEmailField">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_STRIPE_EMAIL'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementStripeAdvancedEmailField" />
                                                </div>
                                            </div>
                                            <!-- STRIPE END -->



                                            <!-- PAYPAL BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypePayPalAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_IPN')); ?>"
                                                        for="bfElementPayPalAdvancedUseIpn">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_USE_IPN'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementPayPalAdvancedUseIpn" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_USE_IPN_DESCRIPTION'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementPayPalAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementPayPalAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_IMAGE')); ?>"
                                                        for="bfElementPayPalAdvancedImage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_IMAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedImage" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_IMAGE')); ?>"
                                                        for="bfElementPayPalAdvancedImageTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_IMAGE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedImageTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_TESTACCOUNT')); ?>"
                                                        for="bfElementPayPalAdvancedTestaccount">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_TESTACCOUNT'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementPayPalAdvancedTestaccount" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_TESTBUSINESS')); ?>"
                                                        for="bfElementPayPalAdvancedTestBusiness">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_TESTBUSINESS'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedTestBusiness" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_TESTTOKEN')); ?>"
                                                        for="bfElementPayPalAdvancedTestToken">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_TESTTOKEN'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedTestToken" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_FILE')); ?>"
                                                        for="bfElementPayPalAdvancedDownloadableFile">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_DOWNLOADABLE_FILE'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementPayPalAdvancedDownloadableFile" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_FILEPATH')); ?>"
                                                        for="bfElementPayPalAdvancedFilepath">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FILEPATH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedFilepath" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_PAYPAL_TRIES')); ?>"
                                                        for="bfElementPayPalAdvancedDownloadTries">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_DOWNLOAD_TRIES'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementPayPalAdvancedDownloadTries" />
                                                </div>
                                            </div>
                                            <!-- PAYPAL END -->
                                            <!-- SOFORTUEBERWEISUNG BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSofortueberweisungAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSofortueberweisungAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_IMAGE')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedImage">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_IMAGE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSofortueberweisungAdvancedImage" />
                                                </div>

                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_IMAGE')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedImageTrans">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_IMAGE'); ?>
                                                        <br /><em>(
                                                            <?php echo $active_language_code ?>)
                                                        </em>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementSofortueberweisungAdvancedImageTrans" />
                                                </div>

                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_FILE')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedDownloadableFile">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_DOWNLOADABLE_FILE'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSofortueberweisungAdvancedDownloadableFile" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_FILEPATH')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedFilepath">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FILEPATH'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementSofortueberweisungAdvancedFilepath" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SOFORT_TRIES')); ?>"
                                                        for="bfElementSofortueberweisungAdvancedDownloadTries">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_DOWNLOAD_TRIES'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementSofortueberweisungAdvancedDownloadTries" />
                                                </div>
                                            </div>
                                            <!-- SOFORTUEBERWEISUNG END -->
                                            <!-- CAPTCHA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCaptchaAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCaptchaAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCaptchaAdvancedHideLabel" />
                                                </div>
                                            </div>
                                            <!-- CAPTCHA END -->
                                            <!-- RECAPTCHA BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeReCaptchaAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementReCaptchaAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementReCaptchaAdvancedHideLabel" />
                                                </div>
                                            </div>
                                            <!-- RECAPTCHA END -->
                                            <!-- CALENDAR RESPONSIVE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCalendarResponsiveAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCalendarResponsiveAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCalendarResponsiveAdvancedHideLabel" />
                                                </div>
                                            </div>
                                            <!-- CALENDAR RESPONSIVE END -->
                                            <!-- CALENDAR BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCalendarAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCalendarAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCalendarAdvancedHideLabel" />
                                                </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_SHOW_TIME_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedShowTime">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_SHOW_TIME'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedShowTime" />
                                                    </div>
                                                    <div class="bfPropertyWrap" style="display:none;">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_FIRST_DAY_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedFirstDay">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_FIRST_DAY'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementCalendarAdvancedFirstDay" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_24H_TIME_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedTimeFormat">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_24H_TIME'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedTimeFormat" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_SINGLE_HEADER_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedSingleHeader">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_SINGLE_HEADER'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedSingleHeader" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_TODAY_BTN_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedTodayButton">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_TODAY_BTN'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedTodayButton" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_WEEK_NMBR_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedWeekNumbers">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_WEEK_NMBR'); ?>
                                                        </label>
                                                        <input checked="checked" type="checkbox" value=""
                                                            id="bfElementCalendarAdvancedWeekNumbers" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_MIN_YEAR_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedMinYear">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_MIN_YEAR'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementCalendarAdvancedMinYear" />
                                                    </div>
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_MAX_YEAR_TOOLTIP')); ?>"
                                                            for="bfElementCalendarAdvancedShowTime">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_QM_CALENDAR_MAX_YEAR'); ?>
                                                        </label>
                                                        <input type="text" value="" id="bfElementCalendarAdvancedMaxYear" />
                                                    </div>
                                            </div>
                                            <!-- CALENDAR END -->
                                            <!-- SIGNATURE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSignatureAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementSignatureAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSignatureAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementSignatureAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSignatureAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementSignatureAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSignatureAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- SIGNATURE END -->
                                            <!-- CHECKBOXGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCheckboxGroupAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCheckboxGroupAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxGroupAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementCheckboxGroupAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxGroupAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementCheckboxGroupAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementCheckboxGroupAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- CHECKBOXGROUP END -->
                                            <!-- CHECKBOX BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeCheckboxAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_MAILBACK_ACCEPT')); ?>"
                                                        for="bfElementCheckboxAdvancedMailbackAccept">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK_ACCEPT'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxAdvancedMailbackAccept" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_MAILBACK_CONNECTWITH')); ?>"
                                                        for="bfElementCheckboxAdvancedMailbackConnectWith">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK_CONNECT_WITH'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementCheckboxAdvancedMailbackConnectWith" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementCheckboxAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementCheckboxAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementCheckboxAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementCheckboxAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementCheckboxAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- CHECKBOX END -->
                                            <!-- CHECKBOXGROUP BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeSelectAdvanced"
                                                style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementSelectAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSelectAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_SELECT_MAILBACK')); ?>"
                                                        for="bfElementSelectAdvancedMailback">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSelectAdvancedMailback" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementSelectAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementSelectAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementSelectAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementSelectAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- CHECKBOXGROUP END -->
                                            <!-- FILE BEGIN -->
                                            <div class="bfElementTypeClass" id="bfElementTypeFileAdvanced" style="display:none">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_HTML5')); ?>"
                                                        for="bfElementFileAdvancedHtml5Uploader">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HTML5_UPLOADER'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementFileAdvancedHtml5Uploader" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_FLASH')); ?>"
                                                        for="bfElementFileAdvancedFlashUploader">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementFileAdvancedFlashUploader" />
                                                    <br />
                                                    <br />
                                                    <i>(
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER_HINT'); ?>
                                                        )
                                                    </i>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_MULTI')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderMulti">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER_MULTI'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementFileAdvancedFlashUploaderMulti" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_BYTES')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderBytes">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER_BYTES'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedFlashUploaderBytes" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_WIDTH')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedFlashUploaderWidth" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_HEIGHT')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderHeight">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER_HEIGHT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedFlashUploaderHeight" />
                                                </div>
                                                <div class="bfPropertyWrap bfTrans">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_TRANSPARENT')); ?>"
                                                        for="bfElementFileAdvancedFlashUploaderTransparent">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER_TRANSPARENT'); ?>
                                                    </label>
                                                    <input type="checkbox" value=""
                                                        id="bfElementFileAdvancedFlashUploaderTransparent" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_UPLOAD_DIRECTORY')); ?>"
                                                        for="bfElementFileAdvancedUploadDirectory">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_UPLOAD_DIRECTORY'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedUploadDirectory" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_RESIZE_WIDTH')); ?>"
                                                        for="bfElementFileAdvancedResizeTargetWidth">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_RESIZE_TARGET_WIDTH'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedResizeTargetWidth"
                                                        value="0" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_RESIZE_HEIGHT')); ?>"
                                                        for="bfElementFileAdvancedResizeTargetHeight">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_RESIZE_TARGET_HEIGHT'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedResizeTargetHeight"
                                                        value="0" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_RESIZE_TYPE')); ?>"
                                                        for="bfElementFileAdvancedResizeType">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_RESIZE_TYPE'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedResizeType" value="" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_RESIZE_BGCOLOR')); ?>"
                                                        for="bfElementFileAdvancedResizeBgcolor">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_RESIZE_BGCOLOR'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedResizeBgcolor"
                                                        value="#ffffff" />
                                                    <br />
                                                    <br />
                                                    <i>(
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_RESIZE_HINT'); ?>)
                                                    </i>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_TIMESTAMP')); ?>"
                                                        for="bfElementFileAdvancedTimestamp">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_TIMESTAMP'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedTimestamp" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_EXTENSIONS')); ?>"
                                                        for="bfElementFileAdvancedAllowedFileExtensions">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ALLOWED_FILE_EXTENSIONS'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementFileAdvancedAllowedFileExtensions" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_ATTACHUSERMAIL')); ?>"
                                                        for="bfElementFileAdvancedAttachToUserMail">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ATTACH_TO_USERMAIL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedAttachToUserMail" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_ATTACHADMINMAIL')); ?>"
                                                        for="bfElementFileAdvancedAttachToAdminMail">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ATTACH_TO_ADMINMAIL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedAttachToAdminMail" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_USEURL')); ?>"
                                                        for="bfElementFileAdvancedUseUrl">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_USE_URL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedUseUrl" />
                                                </div>
                                                <div class="bfPropertyWrap" style="display:none;">
                                                    <!-- legacy and hidden as not required any longer -->
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_FILE_USEURL_DIR')); ?>"
                                                        for="bfElementFileAdvancedUseUrlDownloadDirectory">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_USE_URL_DOWNLOAD_DIRECTORY'); ?>
                                                    </label>
                                                    <input type="text" value=""
                                                        id="bfElementFileAdvancedUseUrlDownloadDirectory" />
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_USE_URL_DOWNLOAD_DIRECTORY_SET_SYNCH'); ?>
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_HIDE_LABEL')); ?>"
                                                        for="bfElementFileAdvancedHideLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_LABEL'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedHideLabel" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LOGGING')); ?>"
                                                        for="bfElementFileAdvancedLogging">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_LOGGING'); ?>
                                                    </label>
                                                    <input checked="checked" type="checkbox" value=""
                                                        id="bfElementFileAdvancedLogging" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_ORDER_NUMBER')); ?>"
                                                        for="bfElementFileAdvancedOrderNumber">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ORDER_NUMBER'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementFileAdvancedOrderNumber" />
                                                </div>
                                            </div>
                                            <!-- FILE END -->
                                            <div id="bfHideInMailback">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_MAILBACK_HIDE')); ?>"
                                                        for="bfElementAdvancedHideInMailback">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_HIDE_IN_MAILBACK'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedHideInMailback" />
                                                </div>
                                            </div>

                                            <div id="bfAdvancedLeaf">
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TABINDEX')); ?>"
                                                        id="bfElementAdvancedTabIndexLabel" for="bfElementAdvancedTabIndex">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_TAB_INDEX'); ?>
                                                    </label>
                                                    <input type="text" value="" id="bfElementAdvancedTabIndex" />
                                                </div>
                                                <div class="bfPropertyWrap">
                                                    <label class="bfPropertyLabel hasTooltip"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_TURNOFF')); ?>"
                                                        for="bfElementAdvancedTurnOff">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_TURN_OFF_INITIALLY'); ?>
                                                    </label>
                                                    <input type="checkbox" value="" id="bfElementAdvancedTurnOff" />
                                                </div>
                                                <div id="bfLabelPositionToggle">
                                                    <div class="bfPropertyWrap">
                                                        <label class="bfPropertyLabel hasTooltip"
                                                            title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ELEMENT_LABELPOS')); ?>"
                                                            id="bfElementAdvancedLabelPositionLabel"
                                                            for="bfElementAdvancedLabelPosition">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_LABEL_POSITION'); ?>
                                                        </label>
                                                        <select id="bfElementAdvancedLabelPosition">
                                                            <option value="left">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_LEFT'); ?>
                                                            </option>
                                                            <option value="top">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_TOP'); ?>
                                                            </option>
                                                            <option value="right">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_RIGHT'); ?>
                                                            </option>
                                                            <option value="bottom">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_BOTTOM'); ?>
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>

                                        <fieldset id="bfInitScript" style="display:none">
                                            <br />
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_ADVANCED_ELEMENT_INITSCRIPT'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <span class="hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_INITSCRIPT')); ?>">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_TYPE') ?>
                                                    :
                                                </span>
                                                <input
                                                    onclick="JQuery('#bfInitScriptFlags').css('display', 'none');
                                                                                            JQuery('#bfInitScriptLibrary').css('display', 'none');
                                                                                            JQuery('#bfInitScriptCustom').css('display', 'none');"
                                                    type="radio" name="initType" id="bfInitTypeNone" class="bfInitType"
                                                    value="0" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_NONE') ?>
                                                <input
                                                    onclick="JQuery('#bfInitScriptFlags').css('display', '');JQuery('#bfInitScriptLibrary').css('display', '');
                                                                                            JQuery('#bfInitScriptCustom').css('display', 'none');"
                                                    type="radio" name="initType" id="bfInitTypeLibrary" class="bfInitType"
                                                    value="1" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_LIBRARY') ?>
                                                <input
                                                    onclick="JQuery('#bfInitScriptFlags').css('display', '');
                                                                                            JQuery('#bfInitScriptLibrary').css('display', 'none');JQuery('#bfInitScriptCustom').css('display', '');"
                                                    type="radio" name="initType" id="bfInitTypeCustom" class="bfInitType"
                                                    value="2" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_CUSTOM') ?>
                                            </div>
                                            <div id="bfInitScriptFlags" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <input type="checkbox" id="bfInitFormEntry" class="bfInitFormEntry"
                                                        name="bfInitFormEntry" value="1" /><label for="bfInitFormEntry"
                                                        class="bfInitFormEntryLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_FORMENTRY'); ?>
                                                    </label>
                                                    <input type="checkbox" id="bfInitPageEntry" class="bfInitPageEntry"
                                                        name="bfInitPageEntry" value="1" /><label for="bfInitPageEntry"
                                                        class="bfInitFormEntryLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_PAGEENTRY'); ?>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="bfInitScriptLibrary" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SCRIPT') ?>:<br /> <select
                                                        id="bfInitScriptSelection"></select>
                                                </div>
                                                <br />
                                                <div id="bfInitSelectionDescription"></div>
                                            </div>

                                            <div id="bfInitScriptCustom" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <div class="hasTooltip btn btn-secondary"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_INITSCRIPT_CODEFRAMEWORK')); ?>"
                                                        style="cursor: pointer;" onclick="createInitCode()">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_CREATE_CODE_FRAMEWORK') ?>
                                                    </div>

                                                    <?php
                                                    echo self::renderCodeMirror('bfInitCode');
                                                    ?>
                                                </div>
                                            </div>
                                        </fieldset>

                                        <fieldset id="bfActionScript" style="display:none">
                                            <br />
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMS_ADVANCED_ELEMENT_ACTIONSCRIPT'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <span class="hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ACTIONSCRIPT')); ?>">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_TYPE') ?>
                                                    :
                                                </span>
                                                <input
                                                    onclick="JQuery('#bfActionScriptFlags').css('display', 'none');
                                                                                            JQuery('#bfActionScriptLibrary').css('display', 'none');
                                                                                            JQuery('#bfActionScriptCustom').css('display', 'none');"
                                                    type="radio" name="actionType" name="actionType" id="bfActionTypeNone"
                                                    class="bfActionType" value="0" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_NONE') ?>
                                                <input
                                                    onclick="JQuery('#bfActionScriptFlags').css('display', '');
                                                                                            JQuery('#bfActionScriptLibrary').css('display', '');
                                                                                            JQuery('#bfActionScriptCustom').css('display', 'none');"
                                                    type="radio" name="actionType" id="bfActionTypeLibrary" class="bfActionType"
                                                    value="1" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_LIBRARY') ?>
                                                <input
                                                    onclick="JQuery('#bfActionScriptFlags').css('display', '');
                                                                                            JQuery('#bfActionScriptLibrary').css('display', 'none');
                                                                                            JQuery('#bfActionScriptCustom').css('display', '');"
                                                    type="radio" name="actionType" id="bfActionTypeCustom" class="bfActionType"
                                                    value="2" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_CUSTOM') ?>
                                            </div>
                                            <div id="bfActionScriptFlags" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_ACTIONS') ?>:
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionClick" name="bfActionClick" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionClickLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CLICK'); ?>
                                                    </label>
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionBlur" name="bfActionBlur" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionBlurLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_BLUR'); ?>
                                                    </label>
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionChange" name="bfActionChange" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionChangeLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_CHANGE'); ?>
                                                    </label>
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionFocus" name="bfActionFocus" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionFocusLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_FOCUS'); ?>
                                                    </label>
                                                    <input style="display:none" type="checkbox" class="bfAction"
                                                        id="bfActionSelect" name="bfActionSelect" value="1" /><label
                                                        style="display:none" class="bfActionLabel" id="bfActionSelectLabel">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_ELEMENTS_SELECTION'); ?>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="bfActionScriptLibrary" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SCRIPT') ?>:<br /><select
                                                        id="bfActionsScriptSelection"></select>
                                                </div>
                                                <br />
                                                <div id="bfActionsScriptSelectionDescription"></div>
                                            </div>

                                            <div id="bfActionScriptCustom" style="display:none">
                                                <hr />
                                                <div class="bfPropertyWrap">
                                                    <div class="hasTooltip btn btn-secondary"
                                                        title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMS_QM_ACTIONSCRIPT_CODEFRAMEWORK')); ?>"
                                                        style="cursor: pointer;" onclick="createActionCode()">
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_CREATE_CODE_FRAMEWORK') ?>
                                                    </div>
                                                    <?php
                                                    echo self::renderCodeMirror('bfActionCode');
                                                    ?>
                                                </div>
                                            </div>

                                        </fieldset>

                                    </div>
                                    <br />
                                    <div class="bfFadingMessage" style="display:none"></div>
                                    <input type="submit" class="btn btn-secondary"
                                        value="<?php echo BFText::_('COM_BREEZINGFORMS_PROPERTIES_SAVE'); ?>"
                                        id="bfAdvancedSaveButton" />
                                    <br />
                                    <br />

                                </div>
                            </div>
                        </div>


                        <div class="b">
                            <div class="b">
                                <div class="b"></div>
                            </div>
                        </div>
                    </div>

                </form>

            </div> <!-- ##### bfQuickModeRight end ##### -->

        </div> <!-- ##### bfQuickModeWrapper end ##### -->

        <?php
    }

    public static function getContent($editor)
    {

        return 'Joomla.editors.instances[' . json_encode($editor) . '].getValue()';
    }

    public static function setContent($editor, $content)
    {

        return 'Joomla.editors.instances[' . json_encode($editor) . '].setValue(' . json_encode($content) . ')';
    }

    public static function renderCodeMirror($name)
    {

        $out = '';
        $params = array('syntax' => 'javascript');
        $editor = Editor::getInstance('codemirror');
        // XDA-GIL - 20240112 - Id mandatory for CodeMirror v6.  
        // $out .= $editor->display($name, '', '100%', 300, 40, 20, false, null, null, null, $params);
        $out .= $editor->display($name, '', '100%', 300, 40, 20, false, $name, null, null, $params);

        $out .= '';

        // XDA-GIL - 20240112 - refresh seems to not exit with CodeMirror v6.  
        /*
        <script>
            jQuery(document).ready(function () {
                setTimeout(function () {
                    Joomla.editors.instances[' . json_encode($name) . '].refresh();
                }, 500);
            });
        </script>';*/

        return $out;
    }

}
