<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Uri\Uri;

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');
require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'pane'.DS.'CBTabs.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'viewlegacy.php');

class ContentbuilderViewElementoptions extends CBView
{
    function display($tpl = null)
    {
        echo '<link rel="stylesheet" href="'.Uri::root(true).'/administrator/components/com_contentbuilder/views/bluestork.fix.css" type="text/css" />';
        
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
