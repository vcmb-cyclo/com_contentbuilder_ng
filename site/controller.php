<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.'/administrator/' .'components/' .'com_contentbuilder/' .'classes/' .'joomla_compat.php');

require_once(JPATH_SITE .'/administrator/components/com_contentbuilder_ng/classes/controllerlegacy.php');

class ContentbuilderController extends CBController
{
    /**
     * Method to display the view
     *
     * @access    public
     */
    function display($cachable = false, $urlparams = array())
    {
        parent::display();
    }

}
