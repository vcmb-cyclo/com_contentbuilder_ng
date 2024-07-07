<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
/**
 * @package     BreezingForms
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

function BreezingformsBuildRoute(&$query)
{

    $segments = array();

    foreach ($query as $key => $value) {
        if (!in_array($key, array('option', 'Itemid', 'lang', 'view', 'form'))) {
            $segments[] = $key;
            $segments[] = $value;
        }
    }

    if (isset($query['form'])) {
        unset($query['form']);
    }

    if (isset($query['view'])) {
        unset($query['view']);
    }

    return $segments;
}

function BreezingformsParseRoute($segments)
{

    $vars = array();

    $last_key = '';
    $seglength = count($segments);
    for ($i = 0; $i < $seglength; $i++) {
        if ($i % 2 == 0) {
            $vars[$segments[$i]] = '';
            $last_key = $segments[$i];
        } else {
            $vars[$last_key] = $segments[$i];
        }
    }
    return $vars;
}
