<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL 
 * @license     GNU/GPL
 */

// no direct access

defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;

require_once (JPATH_SITE .'/administrator/components/com_contentbuilder_ng/classes/joomla_compat.php');
require_once (JPATH_SITE .'/administrator/components/com_contentbuilder_ng/classes/pane/CBTabs.php');
require_once (JPATH_SITE .'/administrator/components/com_contentbuilder_ng/classes/viewlegacy.php');

class ContentbuilderViewElementoptions extends HtmlView
{
    function display($tpl = null)
    {
        echo '<link rel="stylesheet" href="' . Uri::root(true) . '/administrator/components/com_contentbuilder_ng/views/bluestork.fix.css" type="text/css" />';

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
