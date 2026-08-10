<?php

/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @author      XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @copyright   Copyright © 2026 XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace CB\Component\Contentbuilderng\Site\Field;

use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

class CbfilterField extends FormField
{
    protected $type = 'Cbfilter';

    protected function getDatabase(): DatabaseInterface
    {
        return RuntimeContextHelper::getDatabase();
    }

    protected function getInput()
    {
        $selectedFormId = (int) ($this->form?->getValue('form_id', 'params', 0) ?? 0);
        if ($selectedFormId <= 0) {
            $selectedFormId = (int) ($this->form?->getValue('form_id', 'params.settings', 0) ?? 0);
        }
        if ($selectedFormId <= 0 && method_exists($this->form, 'getData')) {
            $data = $this->form->getData();
            if (is_object($data) && method_exists($data, 'get')) {
                $selectedFormId = (int) $data->get('params.form_id', 0);
                if ($selectedFormId <= 0) {
                    $selectedFormId = (int) $data->get('params.settings.form_id', 0);
                }
            }
        }
        if ($selectedFormId <= 0) {
            $selectedFormId = (int) $this->value;
        }

        $out = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8') . '"/>';
        $nativeMenuLayout = (string) ($this->element['menu-layout'] ?? '') === 'native';
        $wrapperId = $this->id . '_elements_wrapper';
        $filterHeader = '';

        if ($nativeMenuLayout) {
            $out .= '<div class="alert alert-info mb-3">' . Text::_('COM_CONTENTBUILDERNG_FILTER_HELP') . '</div>';
            $filterHeader = '<div class="row g-2 mb-2 fw-semibold">'
                . '<div class="col-12 col-md-3">' . htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_FILTER_FIELD_HEADING'), ENT_QUOTES, 'UTF-8') . '</div>'
                . '<div class="col">' . htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_FILTER_VALUE_HEADING'), ENT_QUOTES, 'UTF-8') . '</div>'
                . '<div class="col-auto px-0 ms-2" style="width: 6rem;">' . htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_ORDER_LABEL'), ENT_QUOTES, 'UTF-8') . '</div>'
                . '</div>';
        }

        $out .= '<div id="' . $wrapperId . '" data-cb-menu-filter-fields>';
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select($db->quoteName(['form_id', 'label', 'reference_id']))
            ->from($db->quoteName('#__contentbuilderng_elements'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($query);
        $allElements = $db->loadAssocList();
        $elementsByForm = [];

        foreach ($allElements as $element) {
            $formId = (string) ($element['form_id'] ?? '');
            $referenceId = (string) ($element['reference_id'] ?? '');

            if ($formId === '' || $referenceId === '') {
                continue;
            }

            $elementsByForm[$formId][] = [
                'label' => (string) ($element['label'] ?? ''),
                'reference_id' => $referenceId,
            ];
        }

        $elements = $elementsByForm[(string) $selectedFormId] ?? [];

        if ($selectedFormId > 0) {
            $out .= $filterHeader;

            foreach ($elements as $element) {
                $referenceId = htmlspecialchars($element['reference_id'], ENT_QUOTES, 'UTF-8');

                if ($nativeMenuLayout) {
                    $out .= '<div class="row g-2 align-items-center mb-2">'
                        . '<div class="col-12 col-md-3"><label class="col-form-label" for="element_' . $referenceId . '">' . htmlspecialchars($element['label'], ENT_QUOTES, 'UTF-8') . '</label></div>'
                        . '<div class="col"><input class="form-control" value="" type="text" onchange="contentbuilderng_addValue(\'' . $referenceId . '\',this.value);" name="element_' . $referenceId . '" id="element_' . $referenceId . '"/></div>'
                        . '<div class="col-auto px-0 ms-2" style="width: 6rem;"><input class="form-control" aria-label="' . htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_ORDER_LABEL'), ENT_QUOTES, 'UTF-8') . '" value="" type="number" min="1" max="99" step="1" onchange="contentbuilderng_addOrderValue(\'' . $referenceId . '\',this.value);" name="element_' . $referenceId . '_order" id="element_' . $referenceId . '_order"/></div>'
                        . '</div>';
                } else {
                    $out .= '<div class="mb-2"><label class="w-15">' . htmlspecialchars($element['label'], ENT_QUOTES, 'UTF-8') . '</label> <input class="form-control w-25" style="display:inline-block;" value="" type="text" onchange="contentbuilderng_addValue(\'' . $referenceId . '\',this.value);" name="element_' . $referenceId . '" id="element_' . $referenceId . '"/>';
                    $out .= ' <label class="ms-2 me-1" for="element_' . $referenceId . '_order">' . htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_ORDER_LABEL'), ENT_QUOTES, 'UTF-8') . '</label><input class="form-control w-10" style="display: inline-block;" value="" type="number" min="1" step="1" onchange="contentbuilderng_addOrderValue(\'' . $referenceId . '\',this.value);" name="element_' . $referenceId . '_order" id="element_' . $referenceId . '_order"/></div>';
                }
            }
        } else {
            $out .= '<br/><br/>' . Text::_('COM_CONTENTBUILDERNG_ADD_LIST_VIEW_SELECT_FORM_FIRST');
        }

        $out .= '</div>';
        $out .= '
                <script type="text/javascript">
                <!--
                function contentbuilderng_findField(selectors){
                    for (var i = 0; i < selectors.length; i++) {
                        var field = document.querySelector(selectors[i]);
                        if (field) {
                            return field;
                        }
                    }

                    return null;
                }

                var formField = contentbuilderng_findField([
                    "#jform_params_settings_form_id",
                    "#jform_params_form_id",
                    "[name=\\"jform[params][settings][form_id]\\"]",
                    "[name=\\"jform[params][form_id]\\"]"
                ]);
                var hiddenFilterField = contentbuilderng_findField([
                    "#jform_params_settings_cb_list_filterhidden",
                    "#jform_params_cb_list_filterhidden",
                    "[name=\\"jform[params][settings][cb_list_filterhidden]\\"]",
                    "[name=\\"jform[params][cb_list_filterhidden]\\"]"
                ]);
                var hiddenOrderField = contentbuilderng_findField([
                    "#jform_params_settings_cb_list_orderhidden",
                    "#jform_params_cb_list_orderhidden",
                    "[name=\\"jform[params][settings][cb_list_orderhidden]\\"]",
                    "[name=\\"jform[params][cb_list_orderhidden]\\"]"
                ]);
                var currentFilterField = document.getElementById("' . $this->id . '");
                var wrapper = document.getElementById("' . $wrapperId . '");
                var form_id = formField ? formField.value : "";
                var curr_form_id = "' . $selectedFormId . '";
                var filterElements = ' . json_encode($elementsByForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';
                var emptyFilterMessage = "' . addslashes(Text::_('COM_CONTENTBUILDERNG_ADD_LIST_VIEW_SELECT_FORM_FIRST')) . '";
                var nativeMenuLayout = ' . ($nativeMenuLayout ? 'true' : 'false') . ';
                var filterHeaderHtml = ' . json_encode($filterHeader, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';
                var orderLabelText = ' . json_encode(Text::_('COM_CONTENTBUILDERNG_ORDER_LABEL'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';

                if (typeof cb_value === "undefined") {
                    var cb_value = {};
                }
                if (typeof cb_value_order === "undefined") {
                    var cb_value_order = {};
                }

                function renderFilterFields(selectedFormId){
                    if (!wrapper) {
                        return;
                    }

                    wrapper.innerHTML = "";
                    var elements = filterElements[String(selectedFormId)] || [];
                    if (!elements.length) {
                        wrapper.innerHTML = emptyFilterMessage;
                        return;
                    }

                    if (nativeMenuLayout) {
                        wrapper.insertAdjacentHTML("beforeend", filterHeaderHtml);
                    }

                    elements.forEach(function(element){
                        var referenceId = String(element.reference_id || "");
                        var row = document.createElement("div");
                        row.className = nativeMenuLayout ? "row g-2 align-items-center mb-2" : "mb-2";

                        var label = document.createElement("label");
                        label.className = nativeMenuLayout ? "col-form-label" : "w-15";
                        label.htmlFor = "element_" + referenceId;
                        label.textContent = String(element.label || "");

                        var labelContainer = row;
                        if (nativeMenuLayout) {
                            labelContainer = document.createElement("div");
                            labelContainer.className = "col-12 col-md-3";
                            row.appendChild(labelContainer);
                        }
                        labelContainer.appendChild(label);

                        var value = document.createElement("input");
                        value.className = nativeMenuLayout ? "form-control" : "form-control w-25";
                        if (!nativeMenuLayout) {
                            value.style.display = "inline-block";
                        }
                        value.type = "text";
                        value.name = "element_" + referenceId;
                        value.id = "element_" + referenceId;
                        value.value = cb_value[referenceId] || "";
                        value.addEventListener("change", function(){ contentbuilderng_addValue(referenceId, this.value); });
                        var valueContainer = row;
                        if (nativeMenuLayout) {
                            valueContainer = document.createElement("div");
                            valueContainer.className = "col";
                            row.appendChild(valueContainer);
                        }
                        valueContainer.appendChild(value);

                        if (!nativeMenuLayout) {
                            var orderLabel = document.createElement("label");
                            orderLabel.className = "ms-2 me-1";
                            orderLabel.htmlFor = "element_" + referenceId + "_order";
                            orderLabel.textContent = "' . addslashes(Text::_('COM_CONTENTBUILDERNG_ORDER_LABEL')) . '";
                            row.appendChild(orderLabel);
                        }

                        var order = document.createElement("input");
                        order.className = nativeMenuLayout ? "form-control" : "form-control w-10";
                        if (!nativeMenuLayout) {
                            order.style.display = "inline-block";
                        }
                        order.type = "number";
                        order.setAttribute("aria-label", orderLabelText);
                        order.min = "1";
                        if (nativeMenuLayout) {
                            order.max = "99";
                        }
                        order.step = "1";
                        order.name = "element_" + referenceId + "_order";
                        order.id = "element_" + referenceId + "_order";
                        order.value = cb_value_order[referenceId] || "";
                        order.addEventListener("change", function(){ contentbuilderng_addOrderValue(referenceId, this.value); });
                        var orderContainer = row;
                        if (nativeMenuLayout) {
                            orderContainer = document.createElement("div");
                            orderContainer.className = "col-auto px-0 ms-2";
                            orderContainer.style.width = "6rem";
                            row.appendChild(orderContainer);
                        }
                        orderContainer.appendChild(order);
                        wrapper.appendChild(row);
                    });
                }
                var previousContentbuilderngSetFormId = window.contentbuilderng_setFormId;

                if (currentFilterField && form_id !== "") {
                    currentFilterField.value = form_id;
                }

                if (curr_form_id !== "" && form_id !== "" && curr_form_id != form_id) {
                    if (wrapper) {
                        wrapper.innerHTML = "' . addslashes(Text::_('COM_CONTENTBUILDERNG_ADD_LIST_VIEW_SELECT_FORM_FIRST')) . '";
                    }
                    if (hiddenFilterField) {
                        hiddenFilterField.value = "";
                    }
                    if (hiddenOrderField) {
                        hiddenOrderField.value = "";
                    }
                }

                var currval_splitted = currval.split("\n");
                for(var i = 0; i < currval_splitted.length; i++){
                    if( currval_splitted[i] != "" ){
                        var keyval = currval_splitted[i].split("\t");
                        if( keyval.length == 2 ){
                            cb_value[keyval[0]] = keyval[1];
                            if(document.getElementById("element_"+keyval[0])){
                                document.getElementById("element_"+keyval[0]).value = keyval[1];
                            }
                        }
                    }
                }
                
                var currval_order_splitted = currval_order.split("\n");
                for(var i = 0; i < currval_order_splitted.length; i++){
                    if( currval_order_splitted[i] != "" ){
                        var keyval_order = currval_order_splitted[i].split("\t");
                        if( keyval_order.length == 2 ){
                            cb_value_order[keyval_order[0]] = keyval_order[1];
                            if(document.getElementById("element_"+keyval_order[0]+"_order")){
                                document.getElementById("element_"+keyval_order[0]+"_order").value = keyval_order[1];
                            }
                        }
                    }
                }

                window.contentbuilderng_setFormId = function(form_id){
                    if (typeof previousContentbuilderngSetFormId === "function") {
                        previousContentbuilderngSetFormId(form_id);
                    }

                    if (currentFilterField) {
                        currentFilterField.value = form_id;
                    }
                    if (hiddenFilterField) {
                        hiddenFilterField.value = "";
                    }
                    if (hiddenOrderField) {
                        hiddenOrderField.value = "";
                    }
                    cb_value = {};
                    cb_value_order = {};
                    renderFilterFields(form_id);
                };
                //-->
                </script>';

        return $out;
    }
}
