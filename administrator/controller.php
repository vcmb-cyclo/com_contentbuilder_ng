<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2025 by XDA+GIL
 * @license     GNU/GPL
*/

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
Use Joomla\CMS\Factory;

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

	    if(CBRequest::getVar('market','') == 'true'){
            Factory::getApplication()->redirect('https://breezingforms.vcmb.fr');
        }
    }

}
