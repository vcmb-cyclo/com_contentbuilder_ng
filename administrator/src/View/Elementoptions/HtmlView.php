<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL 
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\Elementoptions;

// no direct access
\defined('_JEXEC') or die('Restricted access');

ini_set('display_errors', 1);
error_reporting(E_ALL);

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    function display($tpl = null)
    {
        echo '<link rel="stylesheet" href="' . Uri::root(true) . '/media/com_contentbuilder/css/bluestork.fix.css" type="text/css" />';

        // Get data from the model
        $element = $this->get('Data');
        $validations = $this->get('ValidationPlugins');
        $this->validations = $validations;
        $this->element = $element;
        $groupdef = $this->get('GroupDefinition');
        $this->group_definition = $groupdef;
        parent::display($tpl);
    }
}
