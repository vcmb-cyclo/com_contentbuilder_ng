<?php

/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */
defined('_JEXEC') or die ('Restricted access');

use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

class JFormFieldCbfilterhidden extends FormField
{

    protected $type = 'Forms';

    protected function getInput()
    {
        $class = $this->element['class'] ? $this->element['class'] : "text_area";
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $out = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $this->value . '"/>' . "\n";
        $out .= '
                <script type="text/javascript">
                <!--
                var cb_value = {};
                var currval = "' . str_replace(array("\n", "\r"), array("\\n", ""), addslashes($this->value)) . '";
                
                function contentbuilder_addValue(element_id, value){
                    cb_value[element_id] = value;
                    var contents = "";
                    for(var x in cb_value){
                        contents += x + "\t" + cb_value[x] + "\n";
                    }
                    document.getElementById("' . $this->id . '").value = contents;
                }
                //-->
                </script>';
        return $out;
    }
}