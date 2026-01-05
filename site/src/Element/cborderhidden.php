<?php

/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Site\Element;

// no direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Form\FormField;

class JFormFieldCborderhidden extends FormField
{

    protected $type = 'Forms';

    protected function getInput()
    {
        $out = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $this->value . '"/>' . "\n";
        $out .= '
                <script type="text/javascript">
                <!--
                var cb_value_order = {};
                var currval_order = "' . str_replace(array("\n", "\r"), array("\\n", ""), addslashes($this->value)) . '";
                
                function contentbuilder_addOrderValue(element_id, value){
                    cb_value_order[element_id] = value;
                    var contents = "";
                    for(var x in cb_value_order){
                        contents += x + "\t" + cb_value_order[x] + "\n";
                    }
                    document.getElementById("' . $this->id . '").value = contents;
                }
                //-->
                </script>';
        return $out;
    }
}